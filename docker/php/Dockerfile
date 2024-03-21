FROM php:8.2-fpm

RUN curl https://deb.nodesource.com/setup_lts.x | bash
RUN curl https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update && apt-get install -y \
        libzip-dev \
        libssl-dev \
        libicu-dev \
        libpcre3-dev \
        libsodium-dev \
        libpq-dev \
        libz-dev \
        libc-client-dev \
        libkrb5-dev \
        curl \
        zip \
        unzip \
        yarn -y && rm -r /var/lib/apt/lists/*

RUN pecl install oauth

RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl && \
    docker-php-ext-install -j$(nproc) imap
RUN docker-php-ext-install pdo pgsql pdo_mysql mysqli zip sodium
RUN docker-php-ext-enable oauth sodium imap

ARG TZ=Europe/Warsaw
ENV TZ ${TZ}
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
RUN curl --insecure https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY symfony.sh /usr/local/bin/_symfony
COPY composer.sh /usr/local/bin/_composer
COPY ./php.ini /usr/local/etc/php/conf.d/php.ini
COPY ./www.conf /usr/local/etc/php-fpm.d/www.conf

RUN chmod +x /usr/local/bin/_symfony
RUN chmod +x /usr/local/bin/_composer