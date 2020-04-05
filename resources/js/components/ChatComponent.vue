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
    var Peer =  require('simple-peer');

    console.log('Component mounted.');
    var chatId = location.pathname.replace('/chat/', '');

    var io = require('socket.io-client');
    var signalserver = io.connect('http://localhost:1337');

    var connections = [];

    signalserver.on('disconnect', function () {
        alert("Server Died!");
        connections = [];
    });

    signalserver.on('connect', function () {
        signalserver.emit('join', chatId);
        console.log("Connected to signal server");
    });

    signalserver.on('inithosts', function (numHosts) {
        console.log("init (" + numHosts + ") hosts");

        for(var i=0;i<numHosts;i++) {
            var host = new Peer({
                initiator: true,
                trickle: false,
            });

            host.on('signal', function (webRtcId) {
                document.getElementById('yourId').value = JSON.stringify(webRtcId);
                signalserver.emit('setconnection', {webRtcId:webRtcId, hostid:host._id});
            });

            connections[host._id] = host;

        }
    });


    signalserver.on('bindtoclient', function (obj) {
        console.log("Bind host " + obj.hostid + " to client");
        document.getElementById('otherId').value = JSON.stringify(obj.webRtcId);

        connections[obj.hostid].signal(obj.webRtcId);

        connections[obj.hostid].on('data', function(data) {
            document.getElementById('messages').textContent += data + '\n';
        });
    });



    signalserver.on('initclient', function (obj) {
        var client = new Peer({
            initiator: false,
            trickle: false,
        });

        //Bind to the host
        client.signal(obj.webRtcId);
        document.getElementById('otherId').value = JSON.stringify(obj.webRtcId);

        //Recieve it's connection details
        client.on('signal', function (webRtcId) {
            console.log("Binding to host..." + obj.hostid);
            document.getElementById('yourId').value = JSON.stringify(webRtcId);
            signalserver.emit('bindconnection', {webRtcId:webRtcId, hostid:obj.hostid, clientid:client._id});

            //client.send("IS IT WORKING?!");
        });

        connections[client._id] = client;

        connections[client._id].on('data', function(data) {
            document.getElementById('messages').textContent += data + '\n';
        });
    });


    document.getElementById('send').addEventListener('click', function() {
        console.log("sending message...");
        var message = document.getElementById('message').value;
        document.getElementById('message').value = '';
        for(var id in connections) {
            connections[id].send(message);
        }

        document.getElementById('messages').textContent += message + '\n';
    });

    /*

    signalserver.on('retry', function () {
        console.log("Retry client conn in 5 seconds");
        setTimeout(function() {
            console.log("Retrying...");
            signalserver.emit('join', chatId);

        }, 5000);
    });

    signalserver.on('inithost', function () {
        var host = new Peer({
            initiator: true,
            trickle: false,
        });

        //Since initiator is set, this is auto-generated on load
        host.on('signal', function (data) {
            console.log("Bound host");
            document.getElementById('yourId').value = JSON.stringify(data);
            signalserver.emit('setlocalconnection', data);
        });

        signalserver.on('bindclient', function (webRtcStr) {
            host.signal(webRtcStr);
            console.log("Conenction established");
        });

        connections.push(host);
    });

    signalserver.on('initclient', function (webRtcStr) {
        var client = new Peer({
            initiator: false,
            trickle: false,
        });

        //Bind to the host
        client.signal(webRtcStr);
        document.getElementById('otherId').value = JSON.stringify(webRtcStr);

        //Recieve it's connection details
        client.on('signal', function (data) {
            console.log("Binding to client" + data);
            document.getElementById('yourId').value = JSON.stringify(data);
            signalserver.emit('setconnection', data);

            //client.send("IS IT WORKING?!");
        });

        connections.push(client);
    });

*/




    /**
     * If you're the initiator then this will be auto-called, otherwise it will be triggered
     * Once we signal the initator string into this peer
     */
    /*peer.on('signal', function (data) {
        document.getElementById('yourId').value = JSON.stringify(data);
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
            //handle the error
            alert(err);
            console.log(err);
        });
    });

    //Get this chat database record
    axios.get('/api/1.0/chats/' + chatId).then(response => {
        var chat = response.data;
    });
*/
}
}




/*

    axios.patch('/api/1.0/chats/' + chatId, {host: data}).then(response => {
        console.log("Set the host");
        console.log(response);
    });
*/
</script>

<style>

</style>