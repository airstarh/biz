FROM php:8.2-fpm AS imag_va_stl_php
USER root
WORKDIR /var/www/html

RUN ln -sf /bin/bash /bin/sh

COPY soft/composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
        iputils-ping \
        dnsutils \
        curl \
        libmagickwand-dev \
        pkg-config \
        git \
        unzip \
        libzip-dev \
        build-essential \
        libssl-dev \
        ca-certificates \
        netcat-traditional \
        default-mysql-client \
        libmariadb-dev \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sSL https://github.com/php/pie/releases/latest/download/pie.phar -o /usr/local/bin/pie && \
    chmod +x /usr/local/bin/pie && \
    pie install imagick/imagick && \
    docker-php-ext-enable imagick

RUN pie install phpredis/phpredis && \
    docker-php-ext-enable redis

RUN docker-php-ext-install pdo pdo_mysql

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

COPY entrypoint.php82.sh /usr/local/bin/entrypoint.sh
RUN chmod 775 /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh && \
    chown www-data:www-data /usr/local/bin/entrypoint.sh

USER www-data

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
