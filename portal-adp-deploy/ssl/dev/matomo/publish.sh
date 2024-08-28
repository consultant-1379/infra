path=armdocker.rnd.ericsson.se/aia/adp/matomo
version=${1:-3.10.0}

docker tag ${path}:local ${path}:${version}
docker push ${path}:${version}
