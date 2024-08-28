mockldap=armdocker.rnd.ericsson.se/aia/adp/node-mailer
version=${1:-latest}

docker tag ${mockldap}:local ${mockldap}:${version}
docker push ${mockldap}:${version}
