# Usar a imagem base do PHP com Apache
FROM php:8.1-apache

# Copiar os arquivos do seu projeto para o diretório do Apache dentro do contêiner
COPY . /var/www/html/

# Modificar a configuração diretamente no Dockerfile
RUN echo "DocumentRoot /var/www/html/src" > /etc/apache2/sites-available/000-default.conf && \
    echo "DirectoryIndex index.php index.html" >> /etc/apache2/sites-available/000-default.conf && \
    echo "<Directory /var/www/html/src>" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    AllowOverride All" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    Require all granted" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    Options Indexes FollowSymLinks" >> /etc/apache2/sites-available/000-default.conf && \
    echo "</Directory>" >> /etc/apache2/sites-available/000-default.conf

# Corrigir permissões para o Apache acessar os arquivos
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Habilitar o módulo de reescrita (caso esteja usando .htaccess)
RUN a2enmod rewrite

# Expor a porta 80 para o Apache
EXPOSE 80

# Iniciar o Apache em modo de primeiro plano
CMD ["apache2-foreground"]
