#! /bin/bash

while getopts ":e:f:n:t:k:" opt; do
  case $opt in
    e) update_type="$OPTARG"
    ;;
    f) env_file="$OPTARG"
    ;;
    n) env_number="$OPTARG"
    ;;
    t) testenv="$OPTARG"
    ;;
    k) private_key_location="$OPTARG"
    ;;    
    \?) echo "Invalid option -$OPTARG" >&2
        echo -e $usage
        exit 1
    ;;
  esac
done

if [ ! -f "backend.env" ]; then
    echo "backend.env not found."
    exit 0
fi

ssh_to_server="ssh -i ${private_key_location} -nq -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no "
scp_to_server="scp -i ${private_key_location} -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no "

if [[ "$update_type" = "insert" ]]; then
        echo "Modifying test compose .env file in place to use new versions"
        while IFS="=" read -r k v; do
                version_key=${k}${env_number}
                api_version=$v
                replace_cmd="grep -q \"^${version_key}=\" ${env_file} && sed -i \"s/^${version_key}=.*/${version_key}=${api_version}/\" ${env_file} || echo \"${version_key}=${api_version}\" >> ${env_file}"
                $ssh_to_server $testenv ${replace_cmd}
                # $ssh_to_server $testenv "sed -i \"s/${version_key}=.*\$/${version_key}=${api_version}/\" $env_file"
        done < backend.env
else
        echo "Replacing entire env file"
        $scp_to_server backend.env ${testenv}:${env_file}
fi