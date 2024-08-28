var express = require('express'),
    path = require('path'),
    nodeMailer = require('nodemailer'),
    bodyParser = require('body-parser');
const https = require('https');
const fs    = require('fs');
var data = {}
const requestLog = {};
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

// Request logger

function requestLoggerMiddleware(tag) {

  return (req, res, next) => {
    const envnum = req.params.envnum;
    if (! envnum) {
      next();
    } else {
      if (!requestLog[envnum]) {
        requestLog[envnum] = [];
      }
      req.tag=tag;
      const filteredRequest = {
        headers: req.headers,
        rawHeaders: req.rawHeaders,
        url: req.url,
        baseUrl: req.baseUrl,
        originalUrl: req.originalUrl,
        method: req.method,
        statusCode: req.statusCode,
        statusMessage: req.statusMessage,
        params: req.params,
        query: req.query,
        body: req.body,
        tag: req.tag
      }
      requestLog[envnum].push(filteredRequest);
      next();
    }
  }
}

app.get("/requestlogger/:envnum/reset", (req, res, next) => {
  const envnum = req.params.envnum;
  if (! envnum) {
    res.sendStatus(500)
  } else {
    requestLog[envnum] = [];
    res.status(200)
    res.send(`Environment ${envnum} cleared.`)
  }
});
app.get("/requestlogger/:envnum/list", (req, res, next) => {
  const envnum = req.params.envnum;
  if (! envnum) {
    res.sendStatus(500);
  } else {
    if (!requestLog[envnum]) {
      requestLog[envnum] = [];
    }
    res.status(200)
    res.send(requestLog[envnum]);
  }
});
app.get("/requestlogger/:envnum/log/:comment", (req, res, next) => {
    const envnum = req.params.envnum;
    const comment = req.params.comment;
    if (! envnum) {
      res.sendStatus(500);
    } else {
      if (!requestLog[envnum]) {
        requestLog[envnum] = [];
      }
      requestLog[envnum].push({comment})
      res.status(200);
      res.send(`Logged ${comment}`);
    }
});


// Artifactory mocker
//

const artState = {};

app.get("/mockartifactory/setfolder/:envnum/:folder", (req, res, next) => {
  artState[req.params.envnum] = req.params.folder;
  res.send(`set folder of ${req.params.envnum} to ${req.params.folder}`);
});

function fixedFolderResolver(req, res, next) {
    const name = req.params.folderName;
    if (!name) {
      res.sendStatus(404);
    } else {
      console.log(name);
      const fullDir = `static/${name}/`;
      readHeaderInfo(req, res, fullDir);
      express.static(fullDir)(req, res, next);
    }
}

function setDefaultValues(res) {
  res.setHeader('x-artifactory-filename', '')
  res.setHeader('last-modified', '')
  res.setHeader('x-checksum-sha1', '')
  res.setHeader('x-checksum-sha256', '')
  res.setHeader('x-checksum-md5', '')
}

function readHeaderInfo(req, res, directory) {
  const fullUrl = req.originalUrl;
  const requestedDir = fullUrl.split('dynamic')
  console.log(requestedDir)
  if (requestedDir && requestedDir.length > 1) {

    const filePath = requestedDir[requestedDir.length-1];
    const fileNameTokens = filePath.split("/");
    console.log(fileNameTokens);
    const fileName = fileNameTokens[fileNameTokens.length-1];
    const filepathRequested = path.join(directory, filePath)
    console.log(fileName);
    console.log(filepathRequested);
    
    try{
      const lmod = fs.readFileSync(`${filepathRequested}.lmod`, 'utf8').replace('\n','');
      console.log(lmod);
      res.setHeader('x-artifactory-filename', fileName)
      res.setHeader('last-modified', lmod)
      res.setHeader('x-checksum-sha1', fs.readFileSync(`${filepathRequested}.sha1`, 'utf8').replace('\n',''))
      res.setHeader('x-checksum-sha256', fs.readFileSync(`${filepathRequested}.sha256`, 'utf8').replace('\n',''))
      res.setHeader('x-checksum-md5', fs.readFileSync(`${filepathRequested}.md5`, 'utf8').replace('\n',''))
    } catch(error) {
      console.log(error);
      setDefaultValues(res);
    }
  } else {
    setDefaultValues(res);
  }
}

function dynamicFolderResolver(req, res, next) {
  console.log("came in as ", req.originalUrl);
  if (req.params.envnum && req.params.envnum in artState) {
    console.log(req.params.envnum);
    const directoryName = artState[req.params.envnum];
    const fullDir = `static/${directoryName}`
    const noFile = req.originalUrl.split("/").join("") === req.baseUrl.split("/").join("");
    console.log(noFile)
    console.log(fullDir)
    // Begin of "Special Server Error Codes"
    // If the scenario calls 'error' plus a valid Server Error Code,
    // the code will answer with the error code required.
    // Examples: 
    // Use /mockartifactory/setfolder/local/error500 for "Internal Server Error"
    // Use /mockartifactory/setfolder/local/error401 for "Unauthorized"
    // Use /mockartifactory/setfolder/local/error429 for "Too Many Requests"
    const fullDirLength = fullDir.length;
    const cmdFolder = fullDir.substr((fullDirLength - 8), 5).toUpperCase();
    const lastThreeDigits = parseInt(fullDir.substr((fullDirLength - 3), 3), 10);
    if ((cmdFolder === 'ERROR') && !(isNaN(lastThreeDigits))) {
      console.log('Sending the Server Code Error:', lastThreeDigits);
      return res.sendStatus(lastThreeDigits);
    }
    // End of "Special Server Error Codes"
    if (noFile) {
      return res.sendFile("index.html", {root: path.join(__dirname, fullDir)});
    }
    readHeaderInfo(req, res, fullDir)
    return express.static(fullDir)(req, res, next);
  } else {
    console.log("sending 404");
    return res.sendStatus(404);
  }
}

app.use("/mockartifactory/:envnum/dynamic", [requestLoggerMiddleware("dynamicArtifactoryRead")] ,dynamicFolderResolver);
app.use("/mockartifactory/:envnum/fixed/:folderName/", [requestLoggerMiddleware("fixedArtifactoryRead")], fixedFolderResolver);



// Nodemailer mocker
//
var port = 3000;
app.get('/', function (req, res) {
  res.render('index');
});

app.post('/:sid/clear', function(req, res){
    var serverId = req.params.sid
    data[serverId]={}
    return res.json({"message":"Cleared"});
});
app.get('/:sid/clear', function(req, res){
    var serverId = req.params.sid
    data[serverId]={}
    return res.json({"message":"Cleared"});
});
app.get('/:sid/emails/', function(req, res){
    var serverId = req.params.sid;
    if (!data[serverId]){
      data[serverId]={};
    }
    return res.json(data[serverId]);
});


var readMessage = (message) => {
  var collect = []
  var html = ""
  Object.keys(message).forEach(key => {
    if(key === 'html') {
      html = message[key]
    } else {
      collect.push(`<li>${key} - ${message[key]} </li>`)
    }
  });
  var formatted = `<div><ul>${collect.join(" ")}</ul><div>${html}</div></div>`
  return formatted;
}

app.get('/:sid/emailspretty/', function(req, res){
    var serverId = req.params.sid;
    if (!data[serverId]){
      data[serverId]={};
    }
    var inboxes = data[serverId];
    var formattedInboxes = [];
    Object.keys(inboxes).forEach(inboxName => {
      var inbox = inboxes[inboxName];
      var formattedMessages = []
      inbox.forEach(message => {
        formattedMessages.push(readMessage(message));
      });
      var formattedInbox = `<li><div><span>${inboxName}</span><div>${formattedMessages.join(" ")}</div></div></li>`;
      formattedInboxes.push(formattedInbox);
    });
    var output = `<ul>${formattedInboxes.join(" ")}</ul>`
    
    return res.send(output);
});

app.post('/:sid/send-email', function (req, res) {
  try {
    var serverId = req.params.sid;
    console.log("received mail for " + serverId);
    if (! (serverId in data)) {
      data[serverId] = {};
    }
    let mailOptions = {
        from: req.body.fromEmailAddress,
        to: req.body.toCsvMailList,
        cc: req.body.ccMailList || [],
        subject: req.body.subject, // Subject line
        text: req.body.messageText,
                    html: req.body.messageHtml // html body
    };
    var logString = `mailerID: ${serverId} to: ${mailOptions.to} cc: ${mailOptions.cc} subject: ${mailOptions.subject}}`;
    console.log(logString);
    var targets;
    var ccTargets;
    if (typeof mailOptions.to == "string")
        targets = mailOptions.to.replace(/ /g,'').split(",");
    else
        targets = mailOptions.to;
    if (typeof mailOptions.cc == "string")
        ccTargets = mailOptions.cc.replace(/ /g,'').split(",");
    else
        ccTargets = mailOptions.cc;
    var allocate = function(el){
      if (! (el in data[serverId])){
          data[serverId][el]=[]
      }
      data[serverId][el].push(
         mailOptions
      )
    }
    targets.forEach(allocate);
    ccTargets.forEach(allocate);

    res.json({"message":"Message successfully sent!"});
  } catch (error) {
    console.log(error);
    res.status(500).send(error);
  }
});

try{
  var privateKey  = fs.readFileSync('/etc/ssl/certs/server.key').toString();
  var certificate = fs.readFileSync('/etc/ssl/certs/server.crt').toString();
  var credentials = {key: privateKey, cert: certificate};
  var appssl = https.createServer(credentials, app);
  appssl.listen(port, function(){
    console.log('Server is running at port: ',port);
  });
} catch (err) {
  console.log("Secure server startup failed, falling back to http");
  app.listen(3000);
}

