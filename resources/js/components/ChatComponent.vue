<template>

  <div class="container">
    <network-graph-component ref="networkGraph"></network-graph-component>

    <strong>Log</strong><br />
    <textarea id="logger" readonly class="form-control" rows=2></textarea>
    <hr />

    <strong>Peer Connections</strong><br />
    <textarea id="connections" readonly class="form-control" rows=5></textarea>
    <hr />

    <label>Message</label><br />

    <pre id="messages"></pre>

    <div class="input-group">
        <input type="text" v-model="message" class="form-control" id="message" v-on:keyup.enter="sendMessage" />

        <span class="input-group-btn">
                <button class="btn btn-primary" type="button"  id="send" v-on:click="sendMessage">
                    <span class="sr-only">Send Message</span>
                    <i class="fas fa-paper-plane"></i>
                </button>
        </span>
    </div>

    <div id='videos'></div>
  </div>
</template>

<script>

//Backfills for Mozilla / Safari
navigator.mediaDevices.getUserMedia = navigator.mediaDevices.getUserMedia ||
navigator.webkitGetUserMedia ||
navigator.mozGetUserMedia;

export default {
    data: function () {
        return {
            message: '',
            connections: [],
            chatId: null,
            server: {ip:'localhost', port:1337, signal: null}
        }
    },
    methods: {
        sendMessage (e) {
            console.log('Called message sender');
            if(this.message != '' && Object.keys(this.connections).length > 0) {
                if(Message.send(this.connections, this.message)) {
                    document.getElementById('messages').textContent += this.message + '\n';
                    this.message = '';
                } else {
                    alert("Something went wrong!");
                }
            }
        },
        outputConnections (cons) {
            var vm = this;

            var networkChartData = {nodes:[{id: 'me', name: 'Me'}], links:[]};

            var txtConnections = document.getElementById('connections');
            txtConnections.textContent = "(" + Object.keys(cons).length + ") open \n";

            for(var id in cons) {
                var host = cons[id];
                if(id == host._id && typeof host.boundClient != 'undefined') {
                    //When you have the host connection
                    networkChartData.nodes.push({id: host.boundClient, name: "C"}); //C For client
                    //networkChartData.nodes.push({id: host.boundClient, name: host.boundClient.substring(0,2)});
                    networkChartData.links.push({source: host.boundClient, target: 'me'});

                    txtConnections.textContent += id + " <- " + host.boundClient + "\n";
                } else {
                    //When you're a client of a host
                    networkChartData.nodes.push({id: id, name: "H"}); //H for host
                    //networkChartData.nodes.push({id: id, name: id.substring(0,2)});
                    networkChartData.links.push({source: 'me', target: id});

                    txtConnections.textContent += host._id + " -> " + id + "\n";
                }

            }
            console.log("Sending to chart");
            console.log(networkChartData);

            vm.$refs.networkGraph.update(networkChartData);

        }
    },
mounted() {
    console.log('Component mounted.');

    //View model reference for inside scoped functions
    var vm = this;

    vm.$refs.networkGraph.init();

    var Peer = require('simple-peer');
    var io = require('socket.io-client');


    vm.chatId = location.pathname.replace('/chat/', '');
    vm.server.signal = io.connect('http://' + vm.server.ip + ':' + vm.server.port);

    var txtLogger = document.getElementById('logger');

    vm.server.signal.on('disconnect', function () {
        alert("Server Died!");
        vm.connections = [];
    });

    vm.server.signal.on('connect', function () {
        vm.server.signal.emit('join', vm.chatId);
        console.log("Connected to signal server");
    });

    vm.server.signal.on('inithosts', function (numHosts) {
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
                vm.connections[this._id] = this;

                txtLogger.textContent += "Signal HostID " + this._id + '\n';
                txtLogger.scrollTop = txtLogger.scrollHeight;

                vm.server.signal.emit('bindtohost', {webRtcId: webRtcId, hostid: this._id});
                vm.outputConnections(vm.connections);
            }).on('close', function() {
                console.log("Destroy connection " + this._id);
                this.destroy();
                delete vm.connections[this._id];

                vm.outputConnections(vm.connections);
            });

            host.on('error', function(err) {
                console.log("Lost connection... Killing host " + this._id);
                this.destroy();
                delete vm.connections[this._id];

                vm.outputConnections(vm.connections);
            });
        }
    });


    vm.server.signal.on('bindtoclient', function (obj) {
        console.log("Bound HostID " + obj.hostid + " to client");
        txtLogger.textContent += "Bound HostID " + obj.hostid + ' to client ' + obj.clientid + '\n';
        txtLogger.scrollTop = txtLogger.scrollHeight;

        if(typeof vm.connections[obj.hostid] != 'undefined' && !vm.connections[obj.hostid].destroyed) {
            vm.connections[obj.hostid].signal(obj.webRtcId);
            vm.connections[obj.hostid].boundClient = obj.clientid;
        }

        vm.outputConnections(vm.connections);
    });


    vm.server.signal.on('initclient', function (obj) {
        //Use the remote host id so that the client is overridden if it re-signals
        var id=obj.hostid;

        console.log("New connection peer object on id " + id);
        vm.connections[id] = new Peer({
            initiator: false
        });

        //Bind to the host
        vm.connections[id].signal(obj.webRtcId);

        txtLogger.textContent += "Bound client " + vm.connections[id]._id + " to host " +  id + '\n';
        txtLogger.scrollTop = txtLogger.scrollHeight;

        //Recieve it's connection details
        vm.connections[id].on('signal', function (webRtcId) {
            console.log("Sending client ("+ vm.connections[id]._id +") connection to host..." + obj.hostid);
            txtLogger.textContent += "Bound to " + obj.hostid + '\n';
            txtLogger.scrollTop = txtLogger.scrollHeight;

            vm.server.signal.emit('bindconnection', {webRtcId:webRtcId, hostid: obj.hostid, clientid: vm.connections[id]._id});
            vm.outputConnections(vm.connections);
        });

        vm.connections[id].on('data', function(data) {
            document.getElementById('messages').textContent += data + '\n';
        });

        vm.connections[id].on('connect', function() {
            txtLogger.textContent += "Client " + vm.connections[id]._id + ' connected to host ' + id + '\n';
            txtLogger.scrollTop = txtLogger.scrollHeight;

            //Set the opened connection
            vm.connections[id] = this;
            vm.outputConnections(vm.connections);
        });

        vm.connections[id].on('close', function() {
            if(typeof(vm.connections[id]) != 'undefined') {
                console.log("Graceful close connection " + vm.connections[id]._id + " -> " + id);
                vm.connections[id].destroy();
            }
            console.log("Close id" + id);
            delete vm.connections[id];
            vm.outputConnections(vm.connections);

        });

        vm.connections[id].on('error', function(err) {
            if(typeof(vm.connections[id]) != 'undefined') {
                console.log("Lost connection... Killing client " + vm.connections[id]._id + " -> " + id);
                vm.connections[id].destroy();
            }
            console.log("Error id" + id);
            delete vm.connections[id];
            vm.outputConnections(vm.connections);
        });

        vm.outputConnections(vm.connections);
    });

}
}



class Message {
    constructor() {}

    static send(connections, message) {
        if(message == '' || Object.keys(connections).length == 0) {
            return false;
        }

        console.log(Object.keys(connections).length + " open connections");

        for(var id in connections) {
            if(connections[id] == null || !connections[id].connected || connections[id].destroyed) {
                console.log("Tried sending through bad connection id " + id);
                console.log(connections);
                console.log(connections[id]);
                console.log("Connected " + connections[id].connected);
                console.log("Destroyed " + connections[id].destroyed);
                delete connections[id];
                continue;
            }

            console.log("Sending to " + id);
            connections[id].send(message);
        }

        //After deleting any bad connections, if there's any left that we sent to then return true
        return Object.keys(connections).length > 0;


    }
}





</script>

<style>

</style>