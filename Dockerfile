FROM php:8.4-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod headers \
    && printf 'expose_php=Off\n' > /usr/local/etc/php/conf.d/saberdizer.ini

COPY docker/apache.conf /etc/apache2/conf-available/saberdizer.conf
RUN a2enconf saberdizer

COPY public/ /var/www/html/
COPY app/ /var/www/app/
COPY bin/ /var/www/bin/
COPY database/ /var/www/database/

EXPOSE 80
