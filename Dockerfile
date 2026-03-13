FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

CMD ["bash", "-lc", "a2dismod mpm_worker mpm_event || true && a2enmod mpm_prefork rewrite && apache2-foreground"]