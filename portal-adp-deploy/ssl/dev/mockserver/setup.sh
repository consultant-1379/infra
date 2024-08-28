./build.sh

cd /local/test-env-setup
docker-compose stop mockserver
docker-compose rm -f mockserver
docker-compose up -d