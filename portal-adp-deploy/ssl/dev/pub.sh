function publish {
cd $1
chmod +x ./build.sh
./publish.sh $2
echo "...."
cd ..
}

publish mockldap 1.0.1 
# publish nodemailer 1.0.1 
publish nodemailer-mock 1.0.1 

#wordpress
