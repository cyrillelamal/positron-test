FROM php:8.1-fpm-alpine3.15

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apk add --no-cache bash && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions intl gd exif pdo_mysql

WORKDIR /var/www/pin-test

EXPOSE 9000

CMD php-fpm --nodaemonize
