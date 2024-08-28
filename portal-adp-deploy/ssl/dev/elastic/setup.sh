./build.sh

cd /local/test-env-setup
docker-compose stop elasticsearch
docker-compose rm -f elasticsearch
docker-compose up -d