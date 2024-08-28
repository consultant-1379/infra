kibana=armdocker.rnd.ericsson.se/aia/adp/kibana
version=${1:-latest}

docker tag ${kibana}:local ${kibana}:${version}
docker push ${kibana}:${version}
