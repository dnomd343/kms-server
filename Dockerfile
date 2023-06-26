FROM alpine:3.18 AS vlmcsd
ENV VLMCSD="svn1113"
RUN apk add build-base
RUN wget https://github.com/Wind4/vlmcsd/archive/refs/tags/${VLMCSD}.tar.gz -qO- | tar xz
WORKDIR ./vlmcsd-${VLMCSD}/
RUN make && mv ./bin/vlmcs ./bin/vlmcsd ./etc/vlmcsd.kmd /tmp/

FROM alpine:3.18 AS asset
RUN apk add php-fpm
WORKDIR /asset/etc/php81/
RUN cat /etc/php81/php-fpm.conf | sed 's/^;\(pid\)/\1/;/error_log/a\error_log = /dev/stdout' > php-fpm.conf
WORKDIR /asset/etc/php81/php-fpm.d/
RUN cat /etc/php81/php-fpm.d/www.conf | sed 's?127.0.0.1:9000?/run/php-fpm.sock?' > www.conf
COPY --from=vlmcsd /tmp/vlmcs* /asset/usr/bin/
#COPY ./nginx/ /asset/etc/nginx/
#COPY ./ /asset/kms-server/
#RUN ln -s /kms-server/kms.php /asset/usr/bin/kms-server

FROM alpine:3.18
RUN apk add --no-cache nginx php php-fpm php-mbstring #php-pcntl php-posix
COPY --from=asset /asset/ /
#EXPOSE 1688/tcp 1689/tcp
#WORKDIR /kms-server/
#ENTRYPOINT ["kms-server"]
