mysql_docker_container=wp-mysql-ssl-test
mysql_user=root
mysql_pass=my-secret-pw
database=wordpress
backup_folder=/local/wpbackup/
docker exec ${mysql_docker_container} /usr/bin/mysqldump --user=${mysql_user} --password=${mysql_pass} ${database} > ${backup_folder}${database}.sql
