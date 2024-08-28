FROM composer:1.9.2

COPY ./composer.json /app/composer.json

WORKDIR /app
RUN composer install && composer update

ENTRYPOINT []