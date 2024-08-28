Scripts for pushing Helm charts to the artifactory
======

Two scripts are available, firstPush.sh is relevant if an index.yaml file is not already pushed to the location you will push to.
push.sh is for subsequent pushes when an index.yaml file is present.

Script Pre-requirements
-----
* Update CHART_FOLDER to equal the path to your chart folder
* Update CHART\_ARM\_PATH to the location you want the Chart to be in the artifactory
* Run the script from the script folder using ./firstPush.sh

firstPush.sh Process
------
1. Packages the chart and retrieves any dependencies if required (creates the tgz file)
2. Generates new index.yaml file
3. Uploads both the index.yaml and tgz files to the artifactory location you specified

push.sh Process
------
1. Packages the chart and retrieves any dependencies if required (creates the tgz file)
2. Fetches the index.yaml from the location you will be pushing to
3. Generates new index.yaml file and merges with existing index.yaml
4. Uploads both the new index.yaml and tgz files to the artifactory location you specified
