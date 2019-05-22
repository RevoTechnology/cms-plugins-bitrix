#!/usr/bin/env bash

docker exec docker-bitrix_fpm_1 bash -c "cd /var/www/local/modules/revo.instalment/ && phpunit"