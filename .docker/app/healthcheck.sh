#!/bin/sh
# Container healthcheck for the php-fpm "app" service.
#
# Boots the application by requesting Laravel's /up health route directly
# through php-fpm over FastCGI. A broken deploy (fatal on boot, bad config
# cache, missing env) returns a non-2xx status here, which marks the task
# unhealthy and lets Swarm's `failure_action: rollback` keep the previous
# container serving instead of taking the API down.
set -u

response=$(REQUEST_METHOD=GET \
    SCRIPT_NAME=/up \
    SCRIPT_FILENAME=/app/public/index.php \
    REQUEST_URI=/up \
    QUERY_STRING= \
    SERVER_PROTOCOL=HTTP/1.1 \
    cgi-fcgi -bind -connect 127.0.0.1:9000 2>/dev/null) || exit 1

# php-fpm emits an explicit "Status:" header only for non-2xx responses;
# a healthy 200 from /up omits it. No output means php-fpm is unreachable.
[ -n "$response" ] || exit 1
printf '%s' "$response" | grep -qiE '^Status:[[:space:]]*[45]' && exit 1
exit 0
