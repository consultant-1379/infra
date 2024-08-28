#!/bin/bash

usage="Usage: (-u) User (-h) Host, (-d) adp deploy directory, (-b) backup directory, (-k) private key for server, (-f) zip file to deploy. (-a) Adp user and (-g) group. (-j) Deployment.json file for use on target."

# Default values
adp_deploy_dir=/local/data/adp

while getopts ":u:h:d:b:k:f:a:g:j:" opt; do
  case $opt in
    u) username="$OPTARG"
    ;;
    a) adp_user="$OPTARG"
    ;;
    g) adp_group="$OPTARG"
    ;;
    h) env_ip="$OPTARG"
    ;;
    d) adp_deploy_dir="$OPTARG"
    ;;
    b) backup_root="$OPTARG"
    ;;
    k) private_key_location="$OPTARG"
    ;;
    f) artifact_location="$OPTARG"
    ;;
    j) json_location="$OPTARG"
    ;;
    \?) echo "Invalid option -$OPTARG" >&2
	echo -e $usage
	exit 1
    ;;
  esac
done

        echo u $username
        echo h $env_ip
        echo d $adp_deploy_dir
        echo b $backup_root
        echo k $private_key_location
        echo f $artifact_location
        echo a $adp_user
        echo g $adp_group
        echo j $json_location

# Checking for empty variables, all are required:
if [[ -z ${username} || -z ${env_ip} || -z ${adp_deploy_dir} || -z ${backup_root} || -z ${private_key_location} || -z ${artifact_location} || -z ${adp_user} || -z ${adp_group} ]];
then
	echo $usage
	exit 1
fi


echo "Will deploy $artifact_location to ${username}@${env_ip}:${adp_deploy_dir} using key at: ${private_key_location} after backup to ${backup_root}. User will be set to ${adp_user}:${adp_group}"

# step 1. Backup the existing adp static content to a /local/adp-backup/adp-${date}/ directory
	backup_dir=${backup_root}/adp-$(date +%Y%m%d-%H%M%S)
	ssh_to_server="ssh -i ${private_key_location} -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no  ${username}@${env_ip}"
        echo "Backing up content of ${adp_deploy_dir} to ${backup_dir} on ${env_ip}"...
        ${ssh_to_server} "sudo -S mkdir -p ${backup_dir}; sudo -S /bin/mv ${adp_deploy_dir}/* ${backup_dir}/ ; sudo -S rm -f /tmp/deploy.zip"

if [ ! $(echo $?) -eq 0 ] ;
then
echo Problem moving existing adp to backup. Aborting!
echo Error doing: ${ssh_to_server} "sudo -S mkdir -p ${backup_dir}; sudo -S /bin/mv ${adp_deploy_dir}/* ${backup_dir}/ ; sudo -S rm -f /tmp/deploy.zip"
exit 1
fi

#step 2. Scp the bundle onto the server to the /tmp/deploy.zip location
        echo "Copying ${artifact_location} to ${env_ip} as /tmp/deploy.zip"...
	scp -i ${private_key_location} -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no ${artifact_location} ${username}@${env_ip}:/tmp/deploy.zip

if [ ! $(echo $?) -eq 0 ] ;
then
echo Problem copying new adp version to server. Aborting!
echo Error doing: scp -i ${private_key_location} ${artifact_location} ${username}@${env_ip}:/tmp/deploy.zip
exit 1
fi

# step 3. Remove the contents of /local/data/adp and unpack the dist zip and place the contents of the dist folder in /local/data/adp/
	echo "Unpacking /tmp/deploy.zip on ${env_ip} and moving the contents of the resulting dist folder to ${adp_deploy_dir}..."
	${ssh_to_server} "sudo -S rm -rf /tmp/dist; unzip -q /tmp/deploy.zip -d /tmp/ ;sudo mv /tmp/dist/* ${adp_deploy_dir}/"
        echo Setting owner of adp contents to ${adp_user}:${adp_group}
        ${ssh_to_server} "sudo -S chown -R ${adp_user}:${adp_group} ${adp_deploy_dir}/*"

if [ ! $(echo $?) -eq 0 ] ;
then
	echo Problem unpacking dist zip to adp mount folder. Aborting!
	echo Error doing: ${ssh_to_server} "sudo -S rm -rf /tmp/dist; unzip -q /tmp/deploy.zip -d /tmp/ ;sudo mv /tmp/dist/* ${adp_deploy_dir}/"
	exit 1
else
	echo "Deployment of ${artifact_location} to ${env_ip} is complete."
fi

# step 4. Copy over the config file for the target environment.
if [[ -n ${json_location} ]]
then
scp -i ${private_key_location} -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no ${json_location} ${username}@${env_ip}:${adp_deploy_dir}/assets/config/production.json
fi
