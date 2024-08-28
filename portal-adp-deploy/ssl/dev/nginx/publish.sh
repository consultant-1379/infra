path=armdocker.rnd.ericsson.se/aia/adp/nginx
version=${1:-1.22.1}

docker tag ${path}:local ${path}:${version}
docker push ${path}:${version}
