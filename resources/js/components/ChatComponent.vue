<template>

    <div class="container">
        <button class="btn btn-primary float-right" type="button"  id="videoToggle" v-on:click="toggleVideo">
            <span class="sr-only" v-if="!stream.enabled">Start Video</span>
            <i class="fas fa-video" v-if="!stream.enabled"></i>

            <span class="sr-only" v-if="stream.enabled">Stop Video</span>
            <i class="fas fa-video-slash" v-if="stream.enabled"></i>
        </button>
        <div id="localVideoContainer"></div>
        <network-graph-component ref="networkGraph"></network-graph-component>


        <div id='videos'></div>
    <!--
        <strong>Log</strong><br />
        <textarea id="logger" readonly class="form-control" rows=1></textarea>
        <hr />

        <strong>Peer Connections</strong><br />
        <textarea id="connections" readonly class="form-control" rows=1></textarea>
        <hr />-->

        <div class="input-group">
            <input type="text" v-model="message" class="form-control" id="message" v-on:keyup.enter="sendMessage" />

            <span class="input-group-btn">
                    <button class="btn btn-primary" type="button"  id="send" v-on:click="sendMessage">
                        <span class="sr-only">Send Message</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
            </span>
        </div>

        <br />

        <div id="messages" class="overflow-auto">
            <div v-for="item in chatLog" :key="item.index">
                <p class="text-muted p-0 mb-0"
                    v-bind:class="{ 'text-right': item.self, 'text-left': !item.self }"
                    v-if="item.index == 0 || (item.index > 0 && chatLog[item.index-1].user.name != item.user.name)">
                    {{item.user.name}}
                    <i class="fas fa-lock" v-if="item.user.verified"></i>
                </p>
                <p class="card p-3 m-1"
                    v-bind:class="{ 'text-right alert-info ml-6': item.self, 'mr-6': !item.self }">
                    {{item.message}}
                </p>
            </div>
        </div>

    </div>
</template>

<style scoped>
    #messages {
        max-height:25vh;
    }
    #localVideoContainer {
        width:155px;
        float:right;
    }
    #localVideoContainer >>> video {
        width:200px;
    }
    #videoToggle {
        float:right;
        position: relative;
        z-index:1;
    }
</style>

<script>

//Backfills for Mozilla / Safari
navigator.mediaDevices.getUserMedia = navigator.mediaDevices.getUserMedia ||
navigator.webkitGetUserMedia ||
navigator.mozGetUserMedia;

export default {
    data: function () {
        return {
            message: '',
            chatLog: [],
            connections: [],
            chatId: null,
            user: null,
            stream: {enabled: false, connection: null, local:null},
            server: {ip:'localhost', port:1337, signal: null}
        }
    },
    methods: {
        toggleVideo(e) {
            var vm = this;
            if(!vm.stream.enabled) {
                navigator.mediaDevices.getUserMedia({
                    video: true,
                    audio: true
                }).then(vm.onLocalStream).catch((e) => {
                    console.log("Local Stream Error!");
                    console.log(e);
                });
            } else {
                vm.stopLocalStream();
            }
        },
        sendMessage (e) {
            var vm = this;
            console.log('Called message sender');
            if(vm.message != '' && Object.keys(vm.connections).length > 0) {
                if(Message.send(vm.connections, vm.message)) {
                    //Write the message we just sent
                    vm.recieveMessage(vm.user, vm.message, true);
                    //document.getElementById('messages').textContent += "Me: " + this.message + '\n';
                    vm.message = '';
                } else {
                    alert("Something went wrong!");
                }
            }
        },
        recieveMessage(user, data, self = false) {
            var vm = this;
            //document.getElementById('messages').textContent += vm.connections[id].user.name + ": " + data + '\n'
            vm.chatLog.push({index: vm.chatLog.length, message: data, user: user, self: self});

            var messageContainer = vm.$el.querySelector("#messages");
            messageContainer.scrollTop = messageContainer.scrollHeight;
        },
        outputConnections (cons) {
            var vm = this;

            var networkChartData = {nodes:[{id: 'me', name: 'Me'}], links:[]};

            //var txtConnections = document.getElementById('connections');
            //txtConnections.textContent = "(" + Object.keys(cons).length + ") open \n";

            for(var id in cons) {
                var host = cons[id];
                var name = typeof host.user != 'undefined' ? host.user.name : 'X';

                //This is the host connection and it's actually bound to someone
                if(typeof host != 'undefined' && id == host._id && typeof host.boundClient != 'undefined') {
                    //When you have the host connection
                    networkChartData.nodes.push({id: host.boundClient, name: name, status: 'client'}); //C For client
                    //networkChartData.nodes.push({id: host.boundClient, name: host.boundClient.substring(0,2)});
                    networkChartData.links.push({source: host.boundClient, target: 'me'});

                    //txtConnections.textContent += id + " <- " + host.boundClient + "\n";
                } else if(typeof host != 'undefined') {

                    //When you're a client of a host
                    networkChartData.nodes.push({id: id, name: name, status: 'host'}); //H for host
                    //networkChartData.nodes.push({id: id, name: id.substring(0,2)});
                    networkChartData.links.push({source: 'me', target: id});

                    //txtConnections.textContent += host._id + " -> " + id + "\n";
                }

            }
            vm.$refs.networkGraph.update(networkChartData);

        },
        onPeerStream(stream) {
            console.log("ON PEER STREAM!!");
            var vm = this;
            var videoContainer = vm.$el.querySelector("#videos");


            // got remote video stream, now let's show it in a video tag
            var video = document.createElement('video');
            //video.muted = true;
            video.controls = true;
            videoContainer.appendChild(video);

            if ('srcObject' in video) {
                video.srcObject = stream
            } else {
                video.src = window.URL.createObjectURL(stream) // for older browsers
            }
            //await new Promise(resolve => video.onloadedmetadata = resolve);
            video.addEventListener("ended", () => console.log("inactive!"));

            video.onloadedmetadata = function (e) {
				video.play();
			};

            console.log(stream);






        },
        onLocalStream(stream) {
            var vm = this;
            vm.stream.enabled = true;
            vm.stream.connection = stream;

            //Send this stream to all the peers
            for(var id in vm.connections) {
                vm.connections[id].addStream(vm.stream.connection);
            }
            var localVideoContainer = vm.$el.querySelector("#localVideoContainer");
            var video = document.createElement('video');
            video.className = 'local-stream';
            video.muted = true;
            localVideoContainer.appendChild(video);

            if ('srcObject' in video) {
                video.srcObject = stream
            } else {
                video.src = window.URL.createObjectURL(stream) // for older browsers
            }

            video.play();
            vm.stream.local = video;
        },
        stopLocalStream() {
            var vm = this;
            vm.stream.enabled = false;

            //Send this stream to all the peers
            for(var id in vm.connections) {
                if(vm.connections[id].streams.length == 0) {
                    continue;
                }
                vm.connections[id].removeStream(vm.stream.connection);
            }

            var localStream = vm.stream.local.srcObject;
            var tracks = localStream.getTracks();

            tracks.forEach(function(track) {
                track.stop();
            });

            vm.stream.connection = null;
            vm.stream.local.srcObject = null;
            vm.$el.querySelector("#localVideoContainer").innerHTML = "";
        },
        init(user) {
            var vm = this;
            vm.user = user;

            var Peer = require('simple-peer');
            var io = require('socket.io-client');


            vm.chatId = location.pathname.replace('/chat/', '');
            vm.server.signal = io.connect('http://' + vm.server.ip + ':' + vm.server.port);

            //var txtLogger = document.getElementById('logger');

            vm.server.signal.on('disconnect', function () {
                alert("Server Died!");
                vm.connections = [];
            });

            vm.server.signal.on('connect', function () {
                console.log("Connected to signal server. Sending auth...");
                //Pass to the server that we want to join this chat room with this user
                //It will use the user to annouce to other connections who you are
                vm.server.signal.emit('join', {chatId: vm.chatId, user: vm.user});
            });

            vm.server.signal.on('inithosts', function (numHosts) {
                console.log("init (" + numHosts + ") hosts");

                for(var i=0;i<numHosts;i++) {
                    console.log("CREATE HOST");
                    var host = new Peer({
                        initiator: true
                    })
                    host.on('data', function(data) {
                        vm.recieveMessage(host.user, data);
                        //document.getElementById('messages').textContent += host.user.name + ": " + data + '\n';
                    });

                    host.on('stream', vm.onPeerStream);

                    host.on('signal', function (webRtcId) {
                        vm.connections[this._id] = this;

                        //txtLogger.textContent += "Signal HostID " + this._id + '\n';
                        //txtLogger.scrollTop = txtLogger.scrollHeight;

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
                //txtLogger.textContent += "Bound HostID " + obj.hostid + ' to client ' + obj.clientid + '\n';
                //txtLogger.scrollTop = txtLogger.scrollHeight;

                if(typeof vm.connections[obj.hostid] != 'undefined' && !vm.connections[obj.hostid].destroyed) {
                    vm.connections[obj.hostid].signal(obj.webRtcId);
                    vm.connections[obj.hostid].user = obj.user;
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

                vm.connections[id].user = obj.user;

                //Bind to the host
                vm.connections[id].signal(obj.webRtcId);

                //txtLogger.textContent += "Bound client " + vm.connections[id]._id + " to host " +  id + '\n';
                //txtLogger.scrollTop = txtLogger.scrollHeight;

                //Recieve it's connection details
                vm.connections[id].on('signal', function (webRtcId) {
                    console.log("Sending client ("+ vm.connections[id]._id +") connection to host..." + obj.hostid);
                    //txtLogger.textContent += "Bound to " + obj.hostid + '\n';
                    //txtLogger.scrollTop = txtLogger.scrollHeight;

                    vm.server.signal.emit('bindconnection', {webRtcId:webRtcId, hostid: obj.hostid, clientid: vm.connections[id]._id});
                    vm.outputConnections(vm.connections);
                });

                vm.connections[id].on('data', function(data) {
                    vm.recieveMessage(vm.connections[id].user, data);
                    //document.getElementById('messages').textContent += vm.connections[id].user.name + ": " + data + '\n';
                });

                vm.connections[id].on('stream', vm.onPeerStream);

                vm.connections[id].on('connect', function() {
                    //txtLogger.textContent += "Client " + vm.connections[id]._id + ' successfully connected to host ' + id + '\n';
                    //txtLogger.scrollTop = txtLogger.scrollHeight;

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
    },
mounted() {
    console.log('Component mounted.');
    //View model reference for inside scoped functions
    var vm = this;

    var user = new User();
    user.then(function(response) {
        vm.init(response.data);
        vm.$refs.networkGraph.init();
    });
}
}

class User {
    //Gets the current authenticated user
    constructor() {
        var name, email, avatar;
        var self = this;
        this.transport = axios.create({
            withCredentials: true
        });

        //Get this chat database record
        return this.transport.get('/api/1.0/users/whoami').then(response => {
            console.log(response.data);
            self.name = response.data.data.name;
            self.email = response.data.data.name;
            self.verified = true;
            return response.data;
        }).catch(error => {
            if (error.response.status === 401) {
                //Prop up an anon user
                return {
                    success: false,
                    message: '',
                    data: {
                        id: null,
                        name: 'Anon Bird',
                        token: null,
                        verified: false
                    }
                }
            }
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