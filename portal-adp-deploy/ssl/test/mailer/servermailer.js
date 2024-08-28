var express = require('express'),
    path = require('path'),
    nodeMailer = require('nodemailer'),
    bodyParser = require('body-parser');
    const https = require('https');
    const fs    = require('fs');
    var data = {}
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
    
    app.post('/clear', function(req, res){
        data={}
	return res.json({"message":"Cleared"});
    });
    app.get('/emails', function(req, res){
        return res.json(data);
    });

    app.post('/send-email', function (req, res) {
	  
      let mailOptions = {
          from: req.body.fromEmailAddress,
          to: req.body.toCsvMailList,
          subject: req.body.subject, // Subject line
          text: req.body.messageText,
		      html: req.body.messageHtml // html body
      };
      console.log(mailOptions);
      var targets;
      if (typeof mailOptions.to == "string")
          targets = mailOptions.to.replace(/ /g,'').split(",");
      else
          targets = mailOptions.to;
      targets.forEach(function(el){
      if (! (el in data)){
          data[el]=[]
      }
      data[el].push(
         mailOptions
      )
      });
      
      res.json({"message":"Message successfully sent!"});
    });
          var privateKey  = fs.readFileSync('/etc/ssl/certs/server.key').toString();
          var certificate = fs.readFileSync('/etc/ssl/certs/server.crt').toString();
          var credentials = {key: privateKey, cert: certificate};
          var appssl = https.createServer(credentials, app);
          appssl.listen(port, function(){
            console.log('Server is running at port: ',port);
          });
