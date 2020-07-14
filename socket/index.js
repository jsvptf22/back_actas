const https = require('https');
const fs = require('fs');

const options = {
    key: fs.readFileSync('certs/netsaia.key'),
    cert: fs.readFileSync('certs/netsaia.cert'),
};

const app = https.createServer(options, function (req, res) {
    res.writeHead(200);
    res.end('hello world');
});

require('./app/socket')(app);

app.listen(process.env.npm_config_port, () => {
    console.log(`listen on localhost:${process.env.npm_config_port}`);
});
