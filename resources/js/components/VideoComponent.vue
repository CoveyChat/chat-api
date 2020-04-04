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
export default {
mounted() {
    navigator.mediaDevices.getUserMedia = navigator.mediaDevices.getUserMedia ||
    navigator.webkitGetUserMedia ||
    navigator.mozGetUserMedia;

    console.log('Component mounted.')
    navigator.mediaDevices.getUserMedia({video: true, audio: true}).then(function(stream) {


    var Peer =  require('simple-peer');
    var peer = new Peer({
        initiator: location.hash === '#init',
        trickle: false,
        stream: stream
    });

    peer.on('signal', function (data) {
        document.getElementById('yourId').value = JSON.stringify(data);
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

    peer.on('data', function(data) {
        document.getElementById('messages').textContent += data + '\n';
    });



    peer.on('stream', function(remoteStream) {
        var video = document.createElement('video');
        document.getElementById('videos').appendChild(video);

        video.srcObject = stream;
        video.play();
    })
    }).catch(function(err) {
        /* handle the error */
        alert(err);
        console.log(err);
    });
}
}
</script>

<style>

</style>