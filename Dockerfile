FROM alpine:3.16 AS vlmcsd
ENV VLMCSD_VERSION="svn1113"
RUN apk add build-base
RUN wget https://github.com/Wind4/vlmcsd/archive/refs/tags/${VLMCSD_VERSION}.tar.gz && tar xf ${VLMCSD_VERSION}.tar.gz
WORKDIR ./vlmcsd-${VLMCSD_VERSION}/
RUN make && mv ./bin/vlmcs ./bin/vlmcsd ./etc/vlmcsd.kmd /tmp/

FROM alpine:3.16 AS iconv
ENV ICONV_VERSION="1.17"
RUN wget https://ftp.gnu.org/pub/gnu/libiconv/libiconv-${ICONV_VERSION}.tar.gz && \
    tar xf libiconv-${ICONV_VERSION}.tar.gz
RUN apk add build-base php8-dev
RUN wget https://php.net/distributions/php-$(php -v | grep -E '^PHP' | awk '{print $2}').tar.gz && \
    tar xf php-*.tar.gz && mv ./php-*/ ./php/
WORKDIR ./libiconv-${ICONV_VERSION}/
RUN ./configure && make && make install && \
    mkdir -p /iconv/local/lib/ && cp -d /usr/local/lib/libiconv.so* /iconv/local/lib/
WORKDIR ../php/ext/iconv/
RUN sed -i '/blahblah/i\return 0;' config.m4 && \
    phpize && ./configure --with-iconv=/usr/local/ && make && \
    mkdir -p /iconv/lib/php8/modules/ && mv ./modules/iconv.so /iconv/lib/php8/modules/

FROM alpine:3.16 AS asset
COPY --from=iconv /iconv/ /asset/usr/
COPY --from=vlmcsd /tmp/vlmcs* /asset/usr/bin/
RUN apk add php8-fpm && mkdir -p /asset/etc/php8/ && \
    sed -i 's/^;\(pid\)/\1/' /etc/php8/php-fpm.conf && \
    mv /etc/php8/php-fpm.conf /asset/etc/php8/
COPY . /asset/kms-server/
RUN mkdir -p /asset/etc/ && mv /asset/kms-server/nginx/ /asset/etc/

FROM alpine:3.16
RUN apk add --no-cache nginx php8 php8-fpm php8-iconv php8-pcntl php8-posix
COPY --from=asset /asset/ /
EXPOSE 1688/tcp 1689/tcp
WORKDIR /kms-server/
ENTRYPOINT ["php", "main.php"]
