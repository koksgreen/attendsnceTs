FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod headers

# Apache serves only /var/www/html/public; includes/ and sql/ stay outside the web root.
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf 'ServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-available/security-extra.conf \
    && a2enconf security-extra

RUN { echo 'expose_php=Off'; echo 'display_errors=Off'; echo 'log_errors=On'; echo 'date.timezone=Africa/Lagos'; } \
    > /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html
COPY includes ./includes
COPY public ./public

# Code is read-only for the web server user.
RUN chown -R root:root /var/www/html && chmod -R a=rX /var/www/html

EXPOSE 80
HEALTHCHECK --interval=15s --timeout=5s --retries=5 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/index.html") === false ? 1 : 0);'
