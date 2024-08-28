mockserver=armdocker.rnd.ericsson.se/aia/adp/mockserver
version=${1:-latest}

docker tag ${mockserver}:local ${mockserver}:${version}
docker push ${mockserver}:${version}
