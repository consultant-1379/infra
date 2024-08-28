
while getopts "u:h:" opt; do
  case $opt in
    u) username="$OPTARG"
    ;;
    h) env_ip="$OPTARG"
    ;;
    *) echo "Invalid option -$OPTARG" >&2
        echo -e $usage
        exit 1
    ;;
  esac
done

if [ -z "$username" ] || [ -z "$env_ip" ]; then
        echo 'Missing -h or -u' >&2
        exit 1
fi

private_key_location=~/.ssh/id_rsa

ssh_to_server="ssh -i ~/.ssh/id_rsa -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no  ${username}@${env_ip}"

${ssh_to_server} "source /local/dock-env/bin/activate; cd /local/cypress; docker-compose down"
${ssh_to_server} "mkdir -p /local/cypress;echo API_URL=${env_ip}>/local/cypress/.env"

scp -i ${private_key_location} -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no docker-compose.yml ${username}@${env_ip}:/local/cypress/docker-compose.yml


${ssh_to_server} "source /local/dock-env/bin/activate; cd /local/cypress; docker-compose up -d"
