FROM php:8.3-fpm

ARG user=joao
ARG uid=1000

RUN apt clean && apt update \
    && apt install -y --no-install-recommends \
    software-properties-common \
    locales \
    openssh-client \
    apt-utils \
    libicu-dev \
    g++ \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    libxslt-dev \
    zip \
    unzip \
    libpq-dev \
    wget \
    curl \
    apt-transport-https \
    lsb-release \
    ca-certificates \
    sshpass \
    gpg-agent

RUN bash -c 'echo "deb https://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list'
RUN wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | apt-key add -
RUN apt update && apt install -y postgresql-client-16 postgresql-contrib-16


RUN apt-get clean && rm -rf /var/lib/apt/lists/*


RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd sockets


COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

RUN pecl install -o -f redis \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis


WORKDIR /var/www


COPY docker/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

USER $user
