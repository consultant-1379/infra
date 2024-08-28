var btoa = require( "btoa" );
var host = process.env.HOST || '0.0.0.0';
var port = process.env.PORT || 8080;
var originBlacklist = parseEnvList(process.env.CORSANYWHERE_BLACKLIST);
var originWhitelist = parseEnvList(process.env.CORSANYWHERE_WHITELIST);
const https = require('https');
const fs    = require('fs');

function parseEnvList(env) {
  if (!env) {
    return [];
  }
  return env.split(',');
}

var privateKey  = fs.readFileSync('/etc/ssl/certs/server.key').toString();
var certificate = fs.readFileSync('/etc/ssl/certs/server.crt').toString();

var cors_proxy = require('cors-anywhere');
cors_proxy.createServer({
  httpsOptions: {
        key: privateKey,
        cert: certificate
  },
  originBlacklist: originBlacklist,
  originWhitelist: [],
  requireHeader: ['origin', 'x-requested-with'],
  removeHeaders: ['cookie'],
  setHeaders: {"Authorization": "Basic " + btoa("eraysel:America3?")}
}).listen(port, host, function() {
  console.log('Running CORS Anywhere on ' + host + ':' + port);
});

var express=require('express');
var nodemailer = require("nodemailer");
const imdb = require('imdb-api');
var app=express();
var smtpTransport = nodemailer.createTransport({
    host: "lmera.ericsson.se",
    port: 25
});

app.get('/',function(req,res){
    res.sendFile('index.html', { root: __dirname });
});
app.get('/send',function(req,res){
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.open( "GET", "http://gerrit-gamma.gic.ericsson.se/a/projects/AIA" , false ); // false for synchronous request
    xmlHttp.send( null );
    return xmlHttp.responseText;
});
/*--------------------Routing Over----------------------------*/

app.listen(3000,function(){
    console.log("Express Started on Port 3000");
});
