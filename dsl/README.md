This outlines the scripts found in the jenkins-scripts directory
The scripts in the jenkins-scripts directory are the script being used for the
Jenkins jobs for Data Science lounge

=====Scripts=====

deploy.sh:
This script is used to deploy the DSL portal to staging via Jenkins
It execute commands remote from Jenkins to staging using ssh
The commands used/process is commented in the script 

it-script.sh
This script runs integration tests on the DSL portal and if it passes
it would call onto a build script to build an image of the DSL portal

build-app.sh
This script is used to build the DSL portal. It pulls down the DSL portal
files and using an Angular cli image it builds the DSL portal and
then cleans up the files used to build and keep the dist folder 
