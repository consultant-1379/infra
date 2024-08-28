#!/bin/bash

usage="Usage: (-u) MySQL user (-h) Host, (-d) mysql database, (-c) Container name/id, (-p) Mysql user password"

# Default values
#wp_host=10.0.2.15
mysql_docker_container=wp-mysql-ssl
mysql_user=root
mysql_pass=my-secret-pw
database=wordpress

while getopts ":u:p:c:d:h:" opt; do
  case $opt in
    u) mysql_user="$OPTARG"
    ;;
    p) mysql_pass="$OPTARG"
    ;;
    c) mysql_docker_container="$OPTARG"
    ;;
    d) database="$OPTARG"
    ;;
    h) wp_host="$OPTARG"
    ;;
    \?) echo "Invalid option -$OPTARG" >&2
	echo -e $usage
	exit 1
    ;;
  esac
done

        echo u $mysql_user
        echo p "-----"
        echo c $mysql_docker_container
        echo d $database
        echo h $wp_host

# Checking for empty variables, all are required:
if [[ -z ${wp_host} ]];
then
	echo $usage
	exit 1
fi


mysql_cmd="docker exec -it ${mysql_docker_container} mysql -u ${mysql_user} -p${mysql_pass} ${database} --execute"

# Set siteurl and home variables:
$mysql_cmd "UPDATE wp_options SET option_value = 'https://${wp_host}' where option_name='home' or option_name='siteurl'"

# Set login/logout redirects to the correct host:
$mysql_cmd "UPDATE wp_login_redirects SET rul_url = 'https://${wp_host}/wp-admin/index.php' where rul_value='administrator'"
$mysql_cmd "UPDATE wp_login_redirects SET rul_url = 'https://${wp_host}/wp-admin/edit.php' where rul_value='author'"
$mysql_cmd "UPDATE wp_login_redirects SET rul_url = 'https://${wp_host}/wp-admin/edit.php?post_type=page' where rul_value='editor'"
$mysql_cmd "UPDATE wp_login_redirects SET rul_url_logout = 'https://${wp_host}/' where rul_value='administrator' OR rul_value='author' OR rul_value='editor'"

$mysql_cmd "select * from wp_login_redirects;"
$mysql_cmd "select option_name, option_value from wp_options where option_name='home' or option_name='siteurl';"

