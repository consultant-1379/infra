version=$1
docker build --tag armdocker.rnd.ericsson.se/aia/adp/portal-wordpress:${version:-latest} .
