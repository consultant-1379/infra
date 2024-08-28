./build.sh

cd /local/test-env-setup
docker-compose stop kibana
docker-compose rm -f kibana
docker-compose up -d