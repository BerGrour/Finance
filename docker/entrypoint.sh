#!/bin/bash
set -e

# Директории Yii2 runtime (логи, кэш, debug) — при монтировании тома с Windows
# права могут мешать записи от www-data. Создаём каталоги и даём права на запись.
RUNTIME_DIR="/var/www/html/runtime"
mkdir -p "${RUNTIME_DIR}/logs"
mkdir -p "${RUNTIME_DIR}/cache"
mkdir -p "${RUNTIME_DIR}/debug"

# На смонтированном с Windows томе chown может не сработать — даём права на запись всем.
chmod -R 777 "${RUNTIME_DIR}"

exec apache2-foreground
