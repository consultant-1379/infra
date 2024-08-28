#!/bin/bash

usage="Usage: (v)ersion: The version to retrieve."
while getopts ":v:r:s" opt; do
  case $opt in
    v) version="$OPTARG"
    ;;
    s) use_snap=true
    ;;
    \?) echo "Invalid option -$OPTARG" >&2
        echo -e $usage
        exit 1
    ;;
  esac
done

echo "(v)ersion $version"
echo "(r)epo_url $repo_url"
echo "(s) use_snap $use_snap"

if [[ -z ${version} ]];
then
	echo "Missing argument"
        echo $usage
        exit 1
fi

srcenv=root@seliius23906.seli.gic.ericsson.se
scp_cmd="scp -i /home/aiaadm100/.ssh/id_rsa"
ssh_cmd="ssh -q -i /home/aiaadm100/.ssh/id_rsa ${srcenv}" 

${scp_cmd} ${srcenv}:/local/data/releases/adp-portal-${version}.zip .