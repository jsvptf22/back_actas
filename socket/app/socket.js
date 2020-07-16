exports = module.exports = function (app) {
    const io = require('socket.io')(app);
    const meetingNms = io.of('/meeting');

    meetingNms.on('connection', function (socket) {
        socket.on('defineRoom', (room) => {
            console.log('join to', room);
            socket.join(room);
        });

        socket.on('updateClients', (request) => {
            meetingNms.in(request.room).emit('refreshClient', request.data);
        });

        socket.on('getData', (managerRoom) => {
            meetingNms.in(managerRoom).emit('getData');
        });

        socket.on('vote', (request) => {
            meetingNms.in(request.room).emit('addVote', request);
        });
    });
};
