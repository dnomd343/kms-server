FROM alpine:3.15.2 as iconv
COPY ./conf/docker/iconv.sh /
RUN sh /iconv.sh

FROM alpine:3.15.2 as vlmcsd
COPY ./conf/docker/vlmcsd.sh /
RUN sh /vlmcsd.sh

FROM alpine:3.15.2
LABEL maintainer="dnomd343"
COPY . /var/www/kms-server
COPY --from=iconv /tmp/iconv/ /usr/local/lib/
COPY --from=vlmcsd /tmp/vlmcsd/ /usr/bin/
RUN apk add --no-cache nginx curl php7 php7-fpm php7-json php7-iconv php7-sqlite3 && \
    rm /usr/lib/php7/modules/iconv.so && ln -s /usr/local/lib/iconv.so /usr/lib/php7/modules/ && \
    mv /usr/local/lib/libiconv.so /usr/local/lib/libiconv.so.2 && \
    mkdir -p /run/nginx && touch /run/nginx/nginx.pid && \
    cp /var/www/kms-server/conf/docker/init.sh / && \
    cp /var/www/kms-server/conf/docker/kms.conf /etc/nginx/kms.conf && \
    cp -f /var/www/kms-server/conf/docker/nginx.conf /etc/nginx/nginx.conf && \
    cp /var/www/kms-server/conf/docker/init.sh /
EXPOSE 1688/tcp 1689/tcp
CMD ["sh","init.sh"]
