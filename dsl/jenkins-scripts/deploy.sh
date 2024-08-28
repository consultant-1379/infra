#!/bin/bash

#Init Variable
KEY=$1
HOST_ADDRESS=$2

chmod 600 $KEY

ssh -oStrictHostKeyChecking=no -i $KEY ubuntu@$HOST_ADDRESS 'sudo rm -rf ./test-area/tmp/*; cp -r ./test-area/dsl-portal/* ./test-area/tmp' 
ssh -oStrictHostKeyChecking=no -i $KEY ubuntu@$HOST_ADDRESS 'cd ./test-area; docker-compose down; rm -rf dsl-portal/*'
scp -oStrictHostKeyChecking=no -i $KEY -r ../dsl/dist/dsl/* ubuntu@$HOST_ADDRESS:~/test-area/dsl-portal/
ssh -oStrictHostKeyChecking=no -i $KEY ubuntu@$HOST_ADDRESS 'cd ./test-area; docker-compose up -d'


