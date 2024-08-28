#!/bin/bash

##BUILD THE IMAGE
docker build -t angular-cli-dsl .
##TAG THE IMAGE
docker tag angular-cli-dsl armdocker.rnd.ericsson.se/aia/dsl/angular-cli-dsl:0.0.1
##PUSH THE IMAGE
docker push armdocker.rnd.ericsson.se/aia/dsl/angular-cli-dsl:0.0.1

echo "Build finished!"
