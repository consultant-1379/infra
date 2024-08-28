path=armdocker.rnd.ericsson.se/aia/adp/angular
version=${1:-7.3.10}

docker tag ${path}:local ${path}:${version}
docker push ${path}:${version}
