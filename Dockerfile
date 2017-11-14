FROM php:5.6-apache

RUN apt-get update && apt-get install -y libmcrypt-dev libicu-dev libcurl4-gnutls-dev libxml2-dev libssl-dev sendmail \
    && docker-php-ext-install -j$(nproc) intl mcrypt curl xml \
    && pecl install mongo \
    && docker-php-ext-enable mongo \
    && a2enmod rewrite \
    && a2enmod headers

COPY . /var/www/html/

VOLUME ["/var/www/html/application/config"]