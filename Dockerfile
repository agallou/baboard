FROM php:5.6

RUN apt-get update && apt-get install -y zlib1g-dev
RUN docker-php-ext-install zip && docker-php-ext-enable zip

RUN apt-get update && apt-get install -y wget git

RUN mkdir /sources
WORKDIR /sources

RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/1b137f8bf6db3e79a38a5bc45324414a6b1f9df2/web/installer -O - -q | php -- --quiet

COPY composer.json /sources/composer.json
COPY composer.lock /sources/composer.lock

RUN php composer.phar install

COPY web /sources/web
COPY Makefile /sources

RUN ls -l web && cd /sources && make assets

WORKDIR /sources/web

CMD php -S 0.0.0.0:80

