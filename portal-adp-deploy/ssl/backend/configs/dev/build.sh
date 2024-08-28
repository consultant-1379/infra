cp -f ./config.json /local/data/config/backend/config.json

# Check environment variables first
if [[ -z "$test_mongo_password" ]]; then
    echo "Must provide test_mongo_password in environment" 1>&2
    exit 1
fi
if [[ -z "$test_tableau_pass" ]]; then
    echo "Must provide test_tableau_pass in environment" 1>&2
    exit 1
fi
if [[ -z "$test_jwt_secret" ]]; then
    echo "Must provide test_jwt_secret in environment" 1>&2
    exit 1
fi
if [[ -z "$test_jwt_integration_secret" ]]; then
    echo "Must provide test_jwt_integration_secret in environment" 1>&2
    exit 1
fi
export config_filename=/local/data/config/backend/config.json
sed -i "s/#mongopass#/${test_mongo_password}/g" ${config_filename} 
sed -i "s/#tableaupass#/${test_tableau_pass}/g" ${config_filename}
sed -i "s/#jwtsecret#/${test_jwt_secret}/g" ${config_filename}
sed -i "s/#jwtintegrationsecret#/${test_jwt_integration_secret}/g" ${config_filename}
sed -i "s/#hostname#/${test_hostname}/g" ${config_filename}
docker restart adp_backend adp_worker nginx
echo "Copied this config file into /local/data/config/backend/config.json and restarted adp_backend, worker and nginx"
