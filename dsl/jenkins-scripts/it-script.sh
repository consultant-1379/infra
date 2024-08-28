##simple test to get a response from the application 
test_build () {
  if [ ! $1 -eq 200 ];
  then
      echo 'build does not work!'
      docker-compose down
      exit 1
  else
      echo 'Got a 200 response!'
      docker-compose down
      #chmod -R g-w dsl-portal
      #./jenkins-scripts/build-app.sh -s
  fi
}

if [ "$1" == "-d" ];
then
  docker-compose -f docker-compose-dev.yml up -d
fi

if [ "$1" == "-p" ];
then
  docker-compose up -d
fi

response=$(curl --write-out %{http_code} --silent --output /dev/null localhost:80)
echo $response
test_build $response
