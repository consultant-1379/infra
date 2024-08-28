usage="Usage: (-u) User (-h) Host, (-k) private key for server, (-a) Adp user and (-g) group. (-d) Where to download the backup tar, (-f) Where to copy the tar to on the target system."


while getopts ":u:a:g:h:k:t:s:q:" opt; do
  case $opt in
    u) username="$OPTARG"
    ;;
    a) adp_user="$OPTARG"
    ;;
    g) adp_group="$OPTARG"
    ;;
    h) env_ip="$OPTARG"
    ;;
    k) private_key_location="$OPTARG"
    ;;
    t) tar_name="$OPTARG"
    ;;
    s) service_name="$OPTARG"
    ;;
    q) is_quiet="$OPTARG"
    ;;
    \?) echo "Invalid option -$OPTARG" >&2
        echo -e $usage
        exit 1
    ;;
  esac
done

        echo u $username
        echo h $env_ip
        echo a $adp_user
        echo g $adp_group
        echo k $private_key_location

        echo t $tar_name
        echo s $service_name
        echo q $is_quiet
# Checking for empty variables, all are required:

if [[ -z ${username} || -z ${env_ip}  || -z ${adp_user} || -z ${adp_group} || -z ${private_key_location} || -z ${tar_name} || -z ${service_name} ]];
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

# SPECIFIC: These are used to unpack backup tars and control docker to spin up and down the database
# tar_name
# service_name=couchdbssl
venv_activate="source /local/dock-env/bin/activate;"
scp_command="scp -i ${private_key_location} -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no ${username}@${env_ip}"
ssh_to_server="ssh -i ${private_key_location} ${is_quiet} -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no  ${username}@${env_ip} ${venv_activate}"
#ssh_to_server=echo
chown_command="chown -R ${adp_user}:${adp_group}"
unpack_dir=${tar_name}_unpacked
echo ""
echo "Unpacking provided fs backup and setting fs permissions"
${ssh_to_server} "mkdir -p ${unpack_dir}; rm -rf ${unpack_dir}/*"
${ssh_to_server} "tar -xf ${tar_name} -C ${unpack_dir}; "
${ssh_to_server} "${chown_command} ${unpack_dir}"
echo ""

echo ""
echo "Performing dump restore"
${ssh_to_server} "docker exec -i ${service_name} sh -c 'mongorestore --authenticationDatabase admin --username admin -p mysecretpassword --drop --archive' < ${unpack_dir}/db.dump"
echo ""


echo "Cleanup..."
${ssh_to_server} "rm -rf ${unpack_dir}"
