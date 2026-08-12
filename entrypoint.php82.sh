#!/bin/sh

echo ""
echo "!!! ENTRYPONT !!!"
echo ""
# This is to be able to change entypoint scripts without re-build.
. /var/www/html/entrypoint.php82.vol.sh
exec "$@"

