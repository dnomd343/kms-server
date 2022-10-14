FROM alpine:3.16 AS vlmcsd
ENV VLMCSD="svn1113"
RUN apk add build-base
RUN wget https://github.com/Wind4/vlmcsd/archive/refs/tags/${VLMCSD}.tar.gz && tar xf ${VLMCSD}.tar.gz
WORKDIR ./vlmcsd-${VLMCSD}/
RUN make && mv ./bin/vlmcs ./bin/vlmcsd ./etc/vlmcsd.kmd /tmp/

FROM alpine:3.16 AS iconv
ENV ICONV="1.17"
RUN apk add build-base php8-dev
RUN wget https://ftp.gnu.org/pub/gnu/libiconv/libiconv-${ICONV}.tar.gz && tar xf libiconv-${ICONV}.tar.gz
RUN wget https://php.net/distributions/php-$(php -v | awk 'NR==1 {print $2}').tar.gz && \
    tar xf php-*.tar.gz php-$(php -v | awk 'NR==1 {print $2}')/ext/iconv --strip-components 2
WORKDIR /libiconv-${ICONV}/
RUN ./configure && make && make install && cp ./lib/.libs/libiconv.so.2 /tmp/
WORKDIR /iconv/
RUN sed -i '/blahblah/i\return 0;' config.m4 && phpize && \
    ./configure --with-iconv=/usr/local && make && mv ./modules/iconv.so /tmp/
RUN strip /tmp/*.so*

FROM alpine:3.16 AS asset
COPY --from=vlmcsd /tmp/vlmcs* /asset/usr/bin/
COPY --from=iconv /tmp/libiconv.so.2 /asset/usr/local/lib/
COPY --from=iconv /tmp/iconv.so /asset/usr/lib/php8/modules/
RUN apk add php8-fpm
WORKDIR /asset/etc/php8/
RUN sed -i 's/^;\(pid\)/\1/' /etc/php8/php-fpm.conf && mv /etc/php8/php-fpm.conf ./
COPY ./nginx/ /asset/etc/nginx/
COPY ./ /asset/kms-server/
RUN ln -s /kms-server/kms.php /asset/usr/bin/kms-server

FROM alpine:3.16
RUN apk add --no-cache nginx php8 php8-fpm php8-iconv php8-pcntl php8-posix
COPY --from=asset /asset/ /
EXPOSE 1688/tcp 1689/tcp
WORKDIR /kms-server/
ENTRYPOINT ["kms-server"]
