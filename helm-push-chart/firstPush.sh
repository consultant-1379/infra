#!/bin/bash
CHART_FOLDER=<pathToChartFolder>
CHART_NAME=$(helm inspect chart ${CHART_FOLDER} | grep -v '^ ' | grep name: | awk '{ print $2 }')
CHART_VERSION=$(helm inspect chart ${CHART_FOLDER}| grep version: | awk '{ print $2 }')
CHART_PACKAGE=${CHART_NAME}-${CHART_VERSION}.tgz
CHART_ARM_PATH=https://armdocker.rnd.ericsson.se/artifactory/proj-helm_aia-generic-local/<path>/<chartName>

# Package the chart
helm package ${CHART_FOLDER}

# Generate new index.yaml
helm repo index --url ${CHART_ARM_PATH} .

# Upload new chart package and index.yaml
curl -k -X PUT "${CHART_ARM_PATH}/${CHART_PACKAGE}" -T ${CHART_PACKAGE}
curl -k -X PUT "${CHART_ARM_PATH}/index.yaml" -T index.yaml