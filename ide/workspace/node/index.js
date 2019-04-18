var http = require('http');

http.createServer(function (req, res) {
  res.writeHead(200, {'Content-Type': 'application/json'});
  var data = { "message" : "Hello World!" };
  res.end(JSON.stringify(data));
}).listen(process.env.PORT, function(){
  console.log("Node.JS API server started at : http://localhost:" + process.env.PORT);
});