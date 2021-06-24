FROM php:7.4-apache
RUN apt-get update; apt-get install -y zip
RUN docker-php-ext-install pdo pdo_mysql

RUN mkdir -p /var/slam_files/temp
RUN chgrp -R www-data /var/slam_files
RUN chmod -R 770 /var/slam_files

COPY . /var/www/html/
