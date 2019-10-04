FROM php:7.1-apache

RUN echo "deb http://deb.debian.org/debian stretch main" >> /etc/apt/sources.list
RUN echo "deb-src http://deb.debian.org/debian stretch main" >> /etc/apt/sources.list

# Install required dependencies
RUN apt-get update && \
    apt-get install -y --no-install-recommends libjpeg-dev git zip unzip libxml2-dev libpng-dev mariadb-client vim sudo wget && \
    rm -rf /var/lib/apt

# Add self signed cert https://letsencrypt.org/docs/certificates-for-localhost/
RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/ssl/private/localhost.key \
    -out /etc/ssl/certs/localhost.crt \
    -subj "/C=DE/ST=/L=/O=Security/OU=Development/CN=example.com"

# configure apache
RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod expires
RUN a2enmod ssl

ADD ./files/000-default.conf /etc/apache2/sites-enabled/
ADD ./files/000-default.ssl.conf /etc/apache2/sites-enabled/

# install composer
RUN php -r 'readfile("https://getcomposer.org/installer");' > composer-setup.php \
  && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
  && rm -f composer-setup.php \
  && chown www-data:www-data /var/www

# Install and enable required php extensions
RUN pecl install xdebug-2.6.0
RUN docker-php-ext-configure gd --with-jpeg-dir=/usr/local/ && \
    docker-php-ext-install mysqli pdo pdo_mysql bcmath soap mbstring dom gd zip intl iconv
RUN docker-php-ext-enable mysqli pdo pdo_mysql bcmath soap mbstring dom gd xdebug zip intl iconv

# configure xdebug
RUN echo 'xdebug.remote_port=9000' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.remote_enable=1' >> /usr/local/etc/php/php.ini
RUN echo 'xdebug.remote_connect_back=1' >> /usr/local/etc/php/php.ini