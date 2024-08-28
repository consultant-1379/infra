export live_host=adp.ericsson.se
export live_user=root
export key_location=/home/aiaadm100/.ssh/id_rsa

action=$1

function stop_all {
	ssh -i ${key_location} ${live_user}@${live_host} "cd ${compose_dir} && docker-compose down"
}

function deploy {
	ssh -i ${key_location} ${live_user}@${live_host} "mkdir -p ${compose_dir}"
	scp -i ${key_location} ./docker-compose.yml ${live_user}@${live_host}:${compose_dir}/docker-compose.yml
}

function start {
	ssh -i ${key_location} ${live_user}@${live_host} "cd ${compose_dir} && docker-compose up -d"
}

if [[ "$action" = "stop" ]];
then
  stop_all
fi

deploy
start
