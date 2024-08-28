cd ./alertmanager
docker build -t armdocker.rnd.ericsson.se/aia/adp/portal/alertmanager:v0.17.0 .

cd ../caddy
docker build -t armdocker.rnd.ericsson.se/aia/adp/portal/caddy .

cd ../cadvisor
docker build -t armdocker.rnd.ericsson.se/aia/adp/portal/cadvisor:v0.36.0 .

cd ../grafana
docker build -t armdocker.rnd.ericsson.se/aia/adp/portal/grafana:6.2.5 .

cd ../nginx-prometheus-exporter
docker build -t armdocker.rnd.ericsson.se/aia/adp/portal/nginx-prometheus-exporter:0.4.2 .

cd ../node-exporter
docker build -t armdocker.rnd.ericsson.se/aia/adp/portal/node-exporter:v0.18.1 .

cd ../prometheus
docker build -t armdocker.rnd.ericsson.se/aia/adp/portal/prometheus:v2.10.0 .

cd ../pushgateway
docker build -t armdocker.rnd.ericsson.se/aia/adp/portal/pushgateway:v0.8.0 .
