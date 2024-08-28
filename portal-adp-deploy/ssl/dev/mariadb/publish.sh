path=armdocker.rnd.ericsson.se/aia/adp/mariadb
version=${1:-10.4.13}

docker tag ${path}:local ${path}:${version}
docker push ${path}:${version}
