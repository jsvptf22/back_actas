const app = require('http').createServer();
require('./socket')(app);

app.listen(8080, () => {
    console.log('listen on localhost:8080');
});
