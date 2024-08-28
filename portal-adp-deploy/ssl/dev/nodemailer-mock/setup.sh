./build.sh

cd /local/test-env-setup
docker-compose stop nodemailerssltest
docker-compose rm -f nodemailerssltest
docker-compose up -d

