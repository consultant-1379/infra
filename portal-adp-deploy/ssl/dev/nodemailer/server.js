var express = require('express'),
    path = require('path'),
    nodeMailer = require('nodemailer'),
    bodyParser = require('body-parser');
    const https = require('https');
    const fs    = require('fs');

    var app = express();
    app.set('view engine', 'ejs');
    app.use(express.static('public'));
    app.use(bodyParser.urlencoded({extended: true}));
    app.use(bodyParser.json());
    app.use(function (req, res, next) {
        res.setHeader('Access-Control-Allow-Origin', '*');
        res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, PATCH');
        res.setHeader('Access-Control-Allow-Headers', 'X-Requested-With,content-type');
    next();
    });
    var port = 3000;
    app.get('/', function (req, res) {
      res.render('index');
    });
    app.post('/send-email', function (req, res) {
      let transporter = nodeMailer.createTransport({
          host: 'smtp-central.internal.ericsson.com',
          port: 25,
          secure: false,
          tls: {
            rejectUnauthorized: false
          }
      });

      let mailOptions = {
          from: req.body.fromEmailAddress,
          to: req.body.toCsvMailList,
          cc: req.body.ccMailList || [],
          subject: req.body.subject, // Subject line
          text: req.body.messageText,
                   html: req.body.messageHtml // html body
      };
      var logString = `to: ${mailOptions.to} cc: ${mailOptions.cc} subject: ${mailOptions.subject}}`;
      console.log(logString);
      transporter.sendMail(mailOptions, (error, info) => {
          if (error) {
              res.json({ message:"Error please check console logs for further details!",error: error });
              return console.log(error);
          }
          res.json({"message":"Message successfully sent!"});
      });
    });
          var privateKey  = fs.readFileSync('/etc/ssl/certs/server.key').toString();
          var certificate = fs.readFileSync('/etc/ssl/certs/server.crt').toString();
          var caInter = fs.readFileSync('/etc/ssl/certs/intermediate.crt').toString();
          var credentials = {key: privateKey, cert: certificate, ca:[caInter]};
          var appssl = https.createServer(credentials, app);
          appssl.listen(port, function(){
            console.log('Server is running at port: ',port);
          });
