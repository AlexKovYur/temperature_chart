FROM php:7.4-fpm

RUN apt-get update && apt-get install -y \
        libpq-dev \
        libicu-dev \
        libzip-dev \
        zip \
        git && \
    docker-php-ext-install -j$(nproc) iconv pdo pdo_pgsql pgsql bcmath intl zip && \
    docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql  && \
    docker-php-ext-enable pdo_pgsql && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
