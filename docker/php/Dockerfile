FROM php:8.1-fpm-alpine3.15

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

COPY wait-for-it.sh /usr/local/bin/wait-for-it.sh

RUN apk add --no-cache bash && \
    chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions intl gd exif pdo_mysql redis && \
    chmod +x /usr/local/bin/wait-for-it.sh && sed -i 's/\r$//' /usr/local/bin/wait-for-it.sh

WORKDIR /var/www/positron

EXPOSE 9000

CMD php-fpm --nodaemonize
