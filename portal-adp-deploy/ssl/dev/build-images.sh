cd portal-base-image
./build.sh 1.0.1
cd ../mockserver
./build.sh
cd ../mockldap
./build.sh
cd ../wordpress
./docker-build.sh 1.0.1
cd ../nodemailer-mock
./build.sh
cd ../elastic
./build.sh
cd ../kibana
./build.sh
cd ../nginx
./build.sh
cd ../mariadb
./build.sh
cd ../matomo
./build.sh
cd ../angular
./build.sh
cd ../mongo
./build.sh 4.4.1
cd ../mongoexpress
./build.sh 0.54.0
