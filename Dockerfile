FROM php:8.1-apache

COPY . /var/www/html/

RUN echo "DocumentRoot /var/www/html/src" > /etc/apache2/sites-available/000-default.conf && \
    echo "DirectoryIndex index.php index.html" >> /etc/apache2/sites-available/000-default.conf && \
    echo "<Directory /var/www/html/src>" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    AllowOverride All" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    Require all granted" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    Options Indexes FollowSymLinks" >> /etc/apache2/sites-available/000-default.conf && \
    echo "</Directory>" >> /etc/apache2/sites-available/000-default.conf

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

RUN a2enmod rewrite

EXPOSE 80

CMD ["apache2-foreground"]
