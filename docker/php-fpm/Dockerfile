FROM phpdockerio/php71-fpm:latest

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install fish phpunit php7.1-redis php7.1-mcrypt php7.1-apcu php7.1-bcmath php7.1-memcached php7.1-memcache php7.1-mongodb php7.1-sqlite3 php7.1-gd \
    php7.1-gmp php7.1-igbinary php7.1-imagick php7.1-imap php7.1-intl php7.1-mbstring php7.1-odbc \
    php7.1-phpdbg php7.1-soap php7.1-ssh2 php7.1-xdebug php7.1-xmlrpc php7.1-xsl \
    mongodb-clients ssh rsync acl xvfb libxrender1 xz-utils wget nodejs nodejs-legacy npm git supervisor && npm install -g bower && npm install -g uglifycss && npm install -g uglify-js && npm install -g less ;\
    apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* ;\
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin && mv /usr/local/bin/composer.phar /usr/local/bin/composer && \
    php -r "unlink('composer-setup.php');" && \
    wget "https://phar.phpunit.de/phpunit-6.2.phar" && chmod +x phpunit-6.2.phar && mv phpunit-6.2.phar /usr/bin/phpunit  && \
    php -r "copy('https://downloads.wkhtmltopdf.org/0.12/0.12.4/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz', 'wkhtmltopdf.tar');" && \
    tar -xvf wkhtmltopdf.tar && mv wkhtmltox/bin/wkhtmltopdf /usr/local/bin/wkhtmltopdf && usermod -s /usr/bin/fish root

CMD ["/bin/bash", "/start.sh"]

WORKDIR "/var/www/mbh"
