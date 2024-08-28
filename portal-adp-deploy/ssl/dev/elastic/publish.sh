elasticsearch=armdocker.rnd.ericsson.se/aia/adp/elasticsearch
version=${1:-latest}

docker tag ${elasticsearch}:local ${elasticsearch}:${version}
docker push ${elasticsearch}:${version}
