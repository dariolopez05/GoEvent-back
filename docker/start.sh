#!/bin/sh

PORT=${PORT:-8000}
sed "s/{{PORT}}/${PORT}/g" /etc/nginx/nginx.conf.template > /etc/nginx/conf.d/default.conf

/usr/bin/supervisord -n
