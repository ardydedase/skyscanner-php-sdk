FROM php:5.4-cli
COPY ../../. /usr/src/phpapp
WORKDIR /usr/src/phpapp

# install composer
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

# install composer packages
RUN composer self-update
RUN composer install --prefer-source --no-interaction