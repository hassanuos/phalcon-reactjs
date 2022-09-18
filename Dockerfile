FROM phalconphp/php-apache:ubuntu-16.04


COPY ./currency-widget /app
WORKDIR /app