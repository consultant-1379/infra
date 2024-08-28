#!/bin/bash

usage="Usage: (-s) mongo service name, (-b) backup directory, (-a) adp user, (-g) adp group"

# Default values
mongo_service=mongo
adp_user=eadpusers
adp_group=eusers

while getopts ":b:a:g:s:" opt; do
  case $opt in
    b) backup_root="$OPTARG"
    ;;
    a) adp_user="$OPTARG"
    ;;
    g) adp_group="$OPTARG"
    ;;
    s) mongo_service="$OPTARG"
    ;;
    \?) echo "Invalid option -$OPTARG" >&2
        echo -e $usage
        exit 1
    ;;
  esac
done

echo b $backup_root
echo a $adp_user
echo g $adp_group
echo s $mongo_service

# Checking for empty variables, all are required:

if [[ -z ${mongo_service} || -z ${backup_root} || -z ${adp_user} || -z ${adp_group} ]];
then
        echo $usage
        exit 1
fi
curdate=$(date +%Y%m%d)
wipedate=$(date +%Y%m%d --date="1 days ago")

backup_dir=${backup_root}/mongodb-backup-${curdate}
tar_file="${backup_dir}.tar.gz"
echo "Dumping mongodb to ${backup_dir} and compressing into ${tar_file}"
mkdir -p ${backup_dir}
docker exec ${mongo_service} sh -c 'mongodump --authenticationDatabase admin --username admin -p mysecretpassword --archive' > ${backup_dir}/db.dump

tar -zcf ${tar_file} -C ${backup_dir} .
chown -R ${adp_user}:${adp_group} ${backup_root}
rm -rf ${backup_root}/mongodb-backup-${curdate}
rm -rf ${backup_root}/mongodb-backup-${wipedate}*

