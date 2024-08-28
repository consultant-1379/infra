mockldap=armdocker.rnd.ericsson.se/aia/adp/mockldap-test
version=${1:-latest}

docker tag ${mockldap}:local ${mockldap}:${version}
docker push ${mockldap}:${version}
