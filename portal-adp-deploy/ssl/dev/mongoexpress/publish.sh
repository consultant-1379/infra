path=armdocker.rnd.ericsson.se/aia/adp/mongoexpress
version=${1:-0.54.0}

docker tag ${path}:local ${path}:${version}
docker push ${path}:${version}
