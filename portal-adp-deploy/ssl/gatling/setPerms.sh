img="armdocker.rnd.ericsson.se/aia/adp/portal-gatling:latest"

# Housekeeping step to change ownership of the results files to logged in user
docker run --rm --entrypoint /bin/sh -v ${PWD}/conf:/opt/gatling/conf -v ${PWD}/user-files:/opt/gatling/user-files -v ${PWD}/results:/opt/gatling/results ${img} -c "chown -R $(id -u):$(id -g) /opt/gatling/"

