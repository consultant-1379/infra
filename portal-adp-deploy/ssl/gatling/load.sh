img="armdocker.rnd.ericsson.se/aia/adp/portal-gatling:latest"
feeder=${4:-config}
user_auth_token=${5}
docker run --rm --net=host -e JAVA_OPTS="-Denv_name=${1:-main} -Dload=${2:-100} -Dtime=${3:-60} -Dfeeder_override=${feeder} -Duser_auth_token=${user_auth_token} -XX:MaxDirectMemorySize=4g" -v ${PWD}/conf:/opt/gatling/conf -v ${PWD}/user-files:/opt/gatling/user-files -v ${PWD}/results:/opt/gatling/results ${img} -rd TEST -s adpportal.BasicSimulation

# Housekeeping step to change ownership of the results files to logged in user
docker run --rm --entrypoint /bin/sh -v ${PWD}/conf:/opt/gatling/conf -v ${PWD}/user-files:/opt/gatling/user-files -v ${PWD}/results:/opt/gatling/results ${img} -c "chown -R $(id -u):$(id -g) /opt/gatling/"

echo "<ul>" > results.html
for l in $(ls -rth results); do echo "<li><a href=results/$l/index.html>$l</a></li>";done >> results.html
echo "</ul>" >> results.html
