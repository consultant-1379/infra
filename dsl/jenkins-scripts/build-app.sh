#!/bin/bash

##Initialize and build the angular app in the vm
git clone ssh://eteemar@gerrit-gamma.gic.ericsson.se:29418/AIA/ui/dsl
cd dsl
git checkout dsl2
echo $1
#docker run --rm -v "$PWD":/dsl -w /dsl armdocker.rnd.ericsson.se/aia/dsl/angular-cli-dsl:test /bin/sh -c "npm install; ng build --prod; chmod -R a+rw dist; chmod -R a+rw node_modules"

##check if the application built
check_dist() {
  if [ -d dist ];
  then
     if [ -z "$(ls -A dist)" ];
     then
        exit 1
     fi
     echo "dist contains minified files!"
     #rm -rf ../dsl-portal/*
     #cp -r dist/dsl/* ../dsl-portal
  else
    exit 1
  fi
echo $1
}



##Pushes the image to the arm repo for use in live
if [ "$1" == "-d" ]
then
    docker run --rm -v "$PWD":/dsl -w /dsl armdocker.rnd.ericsson.se/aia/dsl/angular-cli-dsl:test /bin/sh -c "npm install; ng build; chmod -R a+rw dist; chmod -R a+rw node_modules"
    check_dist
fi

if [ "$1" == "-p" ]
then
    docker run --rm -v "$PWD":/dsl -w /dsl armdocker.rnd.ericsson.se/aia/dsl/angular-cli-dsl:test /bin/sh -c "npm install; ng build --prod; chmod -R a+rw dist; chmod -R a+rw node_modules"
    check_dist
    echo "removing dsl folder"
    #cd ..
    echo $PWD
    rm -rf ../dsl-portal/*
    cp -r dist/dsl/* ../dsl-portal
    cd ..
    rm -rf dsl
    #docker build -t dsl-portal:live .
    #docker tag dsl-portal:live armdocker.rnd.ericsson.se/aia/dsl/dsl-portal:live
    #docker push armdocker.rnd.ericsson.se/aia/dsl/dsl-portal:live
fi



