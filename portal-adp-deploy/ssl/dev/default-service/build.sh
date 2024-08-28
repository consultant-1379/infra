image="armdocker.rnd.ericsson.se/aia/adp/asciidoctorservice:test"
docker build -t ${image} .
docker push ${image}

# image="armdocker.rnd.ericsson.se/aia/adp/otherservice:test"
# docker build -t ${image} .
# docker push ${image}