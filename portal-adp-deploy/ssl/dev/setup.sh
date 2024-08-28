target=/local/test-env-setup
current=$(pwd)

if [ -e ./secrets ]
then
    echo "Secrets found"
    source ./secrets
else
    echo "No secrets file found"
    exit 1
fi

# This step takes down existing env to prevent dangling containers
cd $target
docker-compose down

# Update the compose file.
cd $current
cp -f ./docker-compose.yml /local/test-env-setup/docker-compose.yml

# Bring up environment. This step will fail if either:
# 1. The required environment variables are not already set
# 2. There is no .env file in the same directory containing the correct env vars.
cd $target
docker-compose up -d
cd $current
sleep 3
./init_ldap.sh
