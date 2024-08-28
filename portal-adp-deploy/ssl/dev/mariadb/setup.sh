./build.sh
servicename=testmariadbssl
cd /local/test-env-setup
docker-compose stop ${servicename}
docker-compose rm -f ${servicename}
docker-compose up -d ${servicename}
