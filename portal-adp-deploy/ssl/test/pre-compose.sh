#!/bin/sh
echo Copy the data folders where they are needed for the docker-compose to find them
rm -rf /local/data/test
mkdir -p /local/data/test
cp -rf ./ldap /local/data/test/
cp -rf ./mailer /local/data/test/

