#!/bin/bash

mysql_docker_container=wp-mysql-ssl-test
mysql_user=root
mysql_pass=my-secret-pw
database=wordpress
backup_folder=/local/wpbackup/
restore_file=wprestore.sql
cat ${backup_folder}${restore_file} | docker exec -i ${mysql_docker_container} /usr/bin/mysql --user=${mysql_user} --password=${mysql_pass}  ${database}
