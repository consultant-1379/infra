./build.sh
servicename=matomo
cd /local/test-env-setup
docker-compose stop ${servicename}
docker-compose rm -f ${servicename}
docker-compose up -d ${servicename}
