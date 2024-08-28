
# Update the nginx config (no restart)
cd ../nginx/dev
./setup.sh

# Update the backend config file, restart backend, worker and nginx
cd ../../backend/configs/dev/
source secrets.env
./build.sh

# Update the frontend production.json file
cd ../../../../portal-ui-deploy/jsons
mkdir -p /local/data/adp/assets/config/
/bin/cp -f pdev.json /local/data/adp/assets/config/production.json
/bin/cp -f pdev.json /local/data/adp/assets/config/development.json
