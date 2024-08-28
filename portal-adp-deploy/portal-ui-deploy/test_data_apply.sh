usage="Usage: (-u) User (-h) Host, (-k) private key for server, (-n) the docker container name of the backend which is used to run the data generator."


while getopts ":u:h:k:q:n:m:l:c:p:s:" opt; do
  case $opt in
    u) username="$OPTARG"
    ;;
    h) env_ip="$OPTARG"
    ;;
    k) private_key_location="$OPTARG"
    ;;
    q) is_quiet="$OPTARG"
    ;;
    n) node_name="$OPTARG"
    ;;
    m) td_service_count="$OPTARG"
    ;;
    l) td_user_count="$OPTARG"
    ;;
    c) compose_location="$OPTARG"
    ;;
    p) compose_filename="$OPTARG"
    ;;
    s) service_name="$OPTARG"
    ;;
    \?) echo "Invalid option -$OPTARG" >&2
        echo -e $usage
        exit 1
    ;;
  esac
done

        echo u $username
        echo h $env_ip
        echo k $private_key_location
        echo n ${node_name}
        echo q $is_quiet
        echo m ${td_service_count}
        echo l ${td_user_count}
        echo c $compose_location
        echo p $compose_filename
        echo s $service_name

# Checking for empty variables, all are required:

if [[ -z ${username} || -z ${env_ip} || -z ${private_key_location} || -z ${node_name} || -z ${compose_location} || -z ${compose_filename} || -z ${service_name} ]];
then
        echo $usage
        exit 1
fi

# COMMON: These arguments are used in most scripts to login to remote hosts using ssh and set file ownership
# private_key_location
# username
# env_ip
# adp_user
# adp_group
venv_activate="source /local/dock-env/bin/activate;"
ssh_to_server="ssh -i ${private_key_location} ${is_quiet} -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no  ${username}@${env_ip} ${venv_activate}"

echo ""
echo "Generating test data..."
${ssh_to_server} "docker exec ${node_name} npm run testDataGenerator ${td_service_count} ${td_user_count}"
sleep 5
${ssh_to_server} "cd ${compose_location}; docker restart ${node_name}"
sleep 10
${ssh_to_server} "mkdir -p /local/reports"
${ssh_to_server} "docker cp ${node_name}:/usr/src/app/tools/performanceFeeders/microservices-slugs.csv /local/reports/microservices-slugs.csv"
${ssh_to_server} "ls -l /local; ls -l /local/reports; chown -R 123510:eusers /local/reports"
scp -i ${private_key_location} -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no ${username}@${env_ip}:/local/reports/microservices-slugs.csv .
