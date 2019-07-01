local http = require('http')
local os = require('os')

local port = os.getenv("PORT")

http.createServer(function (req, res)
  local body = "Hello World\n"
  res:setHeader("Content-Type", "text/plain")
  res:setHeader("Content-Length", #body)
  res:finish(body)
end):listen(port, '127.0.0.1')

print('Server running at ' .. port)