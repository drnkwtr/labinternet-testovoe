FROM joseluisq/php-fpm:8.4.7

ARG USER_UID=1000
ARG GROUP_GID=1000
ARG UGNAME=webapp

RUN apk add --no-cache git dumb-init openssh-client tzdata bash nodejs npm postgresql-client fcgi
ENV TZ=Europe/Moscow

RUN mkdir /usr/local/etc/php/conf.d/disabled && \
    mv /usr/local/etc/php/conf.d/docker-php-ext-psr.ini /usr/local/etc/php/conf.d/disabled/docker-php-ext-psr.ini

RUN addgroup --gid "$GROUP_GID" "$UGNAME" && \
    adduser \
        --disabled-password \
        --gecos "" \
        --home "/app" \
        --ingroup "$UGNAME" \
        --no-create-home \
        --uid "$USER_UID" \
        "$UGNAME"

COPY ./.docker/app/entrypoint.sh /docker/entrypoint.sh
COPY ./.docker/app/healthcheck.sh /docker/healthcheck.sh
RUN chmod +x /docker/entrypoint.sh /docker/healthcheck.sh

WORKDIR /app

CMD ["php-fpm"]
ENTRYPOINT ["/docker/entrypoint.sh"]
