<template>
  <div class="container">
    <strong>Log</strong><br />
    <textarea id="logger" readonly class="form-control" rows=5></textarea>
    <hr />

    <strong>Connections</strong><br />
    <textarea id="connections" readonly class="form-control" rows=5></textarea>
    <hr />

    <label>Message</label><br />
    <textarea id="message" class="form-control" rows=1></textarea>
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
    var dumpConnections = function(cons) {
        var txtConnections = document.getElementById('connections');
        txtConnections.textContent = "(" + Object.keys(cons).length + ") open \n";
        for(var id in cons) {
            if(id == cons[id]._id) {
                txtConnections.textContent += "Hosting " + id + "\n";
            } else {
                txtConnections.textContent += cons[id]._id + " -> " + id + "\n";
            }

        }
    };

    var Peer =  require('simple-peer');

    console.log('Component mounted.');
    var chatId = location.pathname.replace('/chat/', '');

    var io = require('socket.io-client');
    var signalserver = io.connect('http://localhost:1337');

    var connections = [];
    var signalIds = {};

    var txtLogger = document.getElementById('logger');

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
            console.log("CREATE HOST");
            var host = new Peer({
                initiator: true
            })
            host.on('data', function(data) {
                document.getElementById('messages').textContent += data + '\n';
            });

            host.on('signal', function (webRtcId) {
                connections[this._id] = this;

                txtLogger.textContent += "Signal HostID " + this._id + '\n';
                txtLogger.scrollTop = txtLogger.scrollHeight;

                signalserver.emit('bindtohost', {webRtcId: webRtcId, hostid: this._id});
                dumpConnections(connections);
            }).on('close', function() {
                console.log("Destroy connection " + this._id);
                dumpConnections(connections);
                this.destroy();
                delete connections[this._id];
            });

            host.on('error', function(err) {
                console.log("Lost connection... Killing host " + this._id);
                this.destroy();
                delete connections[this._id];
            });



        }
    });


    signalserver.on('bindtoclient', function (obj) {
        console.log("Bound HostID " + obj.hostid + " to client");
        txtLogger.textContent += "Bound HostID " + obj.hostid + ' to client ' + obj.clientid + '\n';
        txtLogger.scrollTop = txtLogger.scrollHeight;

        if(typeof connections[obj.hostid] != 'undefined' && !connections[obj.hostid].destroyed) {
            connections[obj.hostid].signal(obj.webRtcId);
        }

        dumpConnections(connections);



    });



    signalserver.on('initclient', function (obj) {
        //Use the remote host id so that the client is overridden if it re-signals
        var id=obj.hostid;

        console.log("New connection peer object on id " + id);
        connections[id] = new Peer({
            initiator: false
        });

        //Bind to the host
        connections[id].signal(obj.webRtcId);

        txtLogger.textContent += "Bound client " + connections[id]._id + " to host " +  id + '\n';
        txtLogger.scrollTop = txtLogger.scrollHeight;

        //Recieve it's connection details
        connections[id].on('signal', function (webRtcId) {
            console.log("Sending client ("+ connections[id]._id +") connection to host..." + obj.hostid);
            txtLogger.textContent += "Bound to " + obj.hostid + '\n';
            txtLogger.scrollTop = txtLogger.scrollHeight;

            signalserver.emit('bindconnection', {webRtcId:webRtcId, hostid: obj.hostid, clientid: connections[id]._id});
            dumpConnections(connections);
        });

        connections[id].on('data', function(data) {
            document.getElementById('messages').textContent += data + '\n';
        });

        connections[id].on('connect', function() {
            txtLogger.textContent += "Client " + connections[id]._id + ' connected to host ' + id + '\n';
            txtLogger.scrollTop = txtLogger.scrollHeight;

            //Set the opened connection
            connections[id] = this;
            dumpConnections(connections);
        });

        connections[id].on('close', function() {
            if(typeof(connections[id]) != 'undefined') {
                console.log("Graceful close connection " + connections[id]._id + " -> " + id);
                connections[id].destroy();
            }
            console.log("Close id" + id);
            delete connections[id];

        });

        connections[id].on('error', function(err) {
            if(typeof(connections[id]) != 'undefined') {
                console.log("Lost connection... Killing client " + connections[id]._id + " -> " + id);
                connections[id].destroy();
            }
            console.log("Error id" + id);
            delete connections[id];
        });

        dumpConnections(connections);
    });


    document.getElementById('send').addEventListener('click', function() {
        console.log("sending message...");
        var message = document.getElementById('message').value;
        document.getElementById('message').value = '';

        console.log(Object.keys(connections).length + " open connections");
        for(var id in connections) {
            if(connections[id] == null || !connections[id].connected || connections[id].destroyed) {
                console.log("Tried sending through bad connection id " + id);
                console.log(connections);
                console.log(connections[id]);
                console.log("Connected " + connections[id].connected);
                console.log("Destroyed " + connections[id].destroyed);

                if(connections[id].destroyed) {
                    delete connections[id];
                }
                continue;
            }

            console.log("Sending through: " + id);
            connections[id].send(message);
        }

        document.getElementById('messages').textContent += message + '\n';
    });

}
}





</script>

<style>

</style>