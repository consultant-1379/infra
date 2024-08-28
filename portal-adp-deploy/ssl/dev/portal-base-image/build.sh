version=$1
docker build --tag armdocker.rnd.ericsson.se/aia/adp/portal-base-image:${version:-latest} .
