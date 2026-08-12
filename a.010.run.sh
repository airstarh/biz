#!/bin/bash
sudo chown -R 33:33 . && \
sudo chmod -R 777 . && \
sudo chmod +x ./entrypoint.php82.vol.sh

docker compose -f docker-compose.yml down
docker compose -f docker-compose.yml build --no-cache --progress=plain 2>&1 | tee a.build.log
docker compose up -d
