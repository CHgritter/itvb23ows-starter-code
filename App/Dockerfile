FROM php:8.2-apache
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
COPY ./src /var/www/html

EXPOSE 80