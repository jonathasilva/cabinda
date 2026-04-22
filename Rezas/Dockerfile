# Base image: Ubuntu 24.04
FROM ubuntu:24.04

# Define usuário root para instalações
USER root
ENV DEBIAN_FRONTEND=noninteractive

# 1. Dependências de Sistema Essenciais
RUN apt-get update && apt-get install -y \
    software-properties-common curl ca-certificates gnupg git unzip lsb-release wget \
    nano htop build-essential \
    && apt-get clean

# 2. Adicionar Repositório Oficial do PHP (Ondrej)
RUN add-apt-repository ppa:ondrej/php -y

# 3. Instalação do Apache e PHP 8.4 com extensões comuns do Laravel
RUN apt-get update && apt-get install -y \
    apache2 \
    php8.4 \
    php8.4-cli \
    php8.4-dev \
    libapache2-mod-php8.4 \
    php8.4-mysql \
    php8.4-mbstring \
    php8.4-curl \
    php8.4-xml \
    php8.4-zip \
    php8.4-intl \
    php8.4-gd \
    php8.4-sqlite3 \
    php8.4-xdebug \
    openssl \
    mysql-client \
    && apt-get clean

# Define o ServerName globalmente para evitar avisos no log
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 4. Instalar Composer (Gerenciador de dependências PHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 5. Configuração do Apache (Habilitar Rewrite para o Laravel)
RUN a2enmod rewrite ssl headers

RUN rm -f /etc/apache2/sites-enabled/000-default.conf

# Configuração do Virtual Host
RUN echo "<VirtualHost *:80>\n\
    ServerName cabinda.local\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    ErrorLog \${APACHE_LOG_DIR}/error.log\n\
    CustomLog \${APACHE_LOG_DIR}/access.log combined\n\
    </VirtualHost>" > /etc/apache2/sites-available/000-default.conf

# Re-habilita o site
RUN a2ensite 000-default.conf

# We copy the certificates to the OS trusted folder. 
# Ubuntu's update-ca-certificates expects .crt extension in this folder.
COPY extras/charlescert.pem /usr/local/share/ca-certificates/charlescert.crt
COPY extras/curlcerts.pem /usr/local/share/ca-certificates/curlcerts.crt

RUN update-ca-certificates

# We need to tell PHP explicitly where the bundle is located.
# The update-ca-certificates command generates a bundle at /etc/ssl/certs/ca-certificates.crt
RUN sed -i 's/;curl.cainfo =/curl.cainfo = "\/etc\/ssl\/certs\/ca-certificates.crt"/g' /etc/php/8.4/apache2/php.ini && \
    sed -i 's/;openssl.cafile=/openssl.cafile= "\/etc\/ssl\/certs\/ca-certificates.crt"/g' /etc/php/8.4/apache2/php.ini && \
    sed -i 's/;curl.cainfo =/curl.cainfo = "\/etc\/ssl\/certs\/ca-certificates.crt"/g' /etc/php/8.4/cli/php.ini && \
    sed -i 's/;openssl.cafile=/openssl.cafile= "\/etc\/ssl\/certs\/ca-certificates.crt"/g' /etc/php/8.4/cli/php.ini

WORKDIR /var/www/html

# Permissões corretas para o Apache
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80 443

CMD ["apache2ctl", "-D", "FOREGROUND"]

