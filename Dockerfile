FROM php:cli-alpine as app

COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN \
    install-php-extensions \
        pdo_mysql  \
        pdo_pgsql  \
        xdebug  \
        @composer  \
    && apk add \
        make \
        bash
        