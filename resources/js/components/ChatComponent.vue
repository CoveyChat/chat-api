<template>
  <div class="container">

    <label>Local ID</label><br />
    <textarea id="yourId" class="form-control" readonly></textarea><br />
    <hr />
    <label>Remote ID</label><br />
    <textarea id="otherId" class="form-control" ></textarea><br />
    <hr />
    <button id="connect" class="btn btn-outline-primary">Connect</button>
    <br /><br />
    <hr />

    <label>Message</label><br />
    <textarea id="message" class="form-control" ></textarea>
    <button id="send" class="btn btn-outline-primary">Send</button>
    <pre id="messages"></pre>

    <div id='videos'>

    </div>
  </div>
</template>

<script>

//Backfills for Mozilla / Safari
navigator.mediaDevices.getUserMedia = navigator.mediaDevices.getUserMedia ||
navigator.webkitGetUserMedia ||
navigator.mozGetUserMedia;

export default {
mounted() {
    console.log('Component mounted.')
    var chatId = location.pathname.replace('/chat/', '');


    var io = require('socket.io-client');
    var signalserver = io.connect('http://localhost:1337');

    signalserver.on('connect', function () {
        signalserver.emit('joinroom', chatId);
        console.log("Connected to signal server");
    });

    signalserver.on('inithost', function (webRtcStr) {


    });

    //Get this chat database record
    axios.get('/api/1.0/chats/' + chatId).then(response => {
        var chat = response.data;
    });



        var Peer =  require('simple-peer');
        var peer = new Peer({
            initiator: location.hash === "#1",
            trickle: false,
        });

        peer.on('signal', function (data) {
            document.getElementById('yourId').value = JSON.stringify(data);

            if(isHost) {
                axios.patch('/api/1.0/chats/' + chatId, {host: data}).then(response => {
                    console.log("Set the host");
                    console.log(response);
                });
            }
        });

        document.getElementById('connect').addEventListener('click', function() {
            var otherId = JSON.parse(document.getElementById('otherId').value);
            console.log("Attempting to connect...");
            peer.signal(otherId);
        });


        document.getElementById('send').addEventListener('click', function() {
            console.log("sending message...");
            var message = document.getElementById('message').value;
            document.getElementById('messages').textContent += message + '\n';
            peer.send(message);
        });


        document.getElementById('startvideo').addEventListener('click', function() {
            console.log("starting video...");

            navigator.mediaDevices.getUserMedia({video: true, audio: true}).then(function(stream) {
                peer.addStream(stream);
            }).catch(function(err) {
                /* handle the error */
                alert(err);
                console.log(err);
            });
        });

        peer.on('data', function(data) {
            document.getElementById('messages').textContent += data + '\n';
        });



        peer.on('stream', function(remoteStream) {
            var video = document.createElement('video');
            document.getElementById('videos').appendChild(video);

            video.srcObject = stream;
            video.play();
        });


}

}
</script>

<style>

</style>