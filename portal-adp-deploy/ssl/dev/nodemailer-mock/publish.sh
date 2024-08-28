nodemailer=armdocker.rnd.ericsson.se/aia/adp/adp-nodemailer-mock
version=${1:-latest}

docker tag ${nodemailer}:local ${nodemailer}:${version}
docker push ${nodemailer}:${version}
