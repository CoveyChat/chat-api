<template>
    <div class="d-flex flex-column w-100">
    <div id="user-prompt" v-if="!user.active" name="fade" class="flex-row flex-grow-1">
        <div class="row">
            <div class="offset-md-4 col-md-4 ">
                <div class="card card-body p-5">
                    <input type="text"
                    class="form-control form-control-xl text-center"
                    placeholder="Enter your name"
                    v-model="ui.anonUsername"
                    v-on:keyup.enter="setAnonUser" />
                    <transition name="fade">
                    <button class="btn btn-lg btn-outline-primary btn-block mt-3" v-on:click="setAnonUser" v-if="ui.anonUsername.length >= 1">
                        Start Chatting
                    </button>
                    </transition>
                </div>
            </div>
        </div>
    </div>
    <div class="chat-container p-5 flex-row"  v-if="user.active">
        <button class="btn btn-primary float-right"
            type="button"  id="videoToggle"
            v-bind:class="{ 'local-video-overlay': ui.inFullscreen }"
            v-on:click="toggleVideo">
            <span class="sr-only" v-if="!stream.enabled">Start Video</span>
            <i class="fas fa-video" v-if="!stream.enabled"></i>

            <span class="sr-only" v-if="stream.enabled">Stop Video</span>
            <i class="fas fa-video-slash" v-if="stream.enabled"></i>
        </button>
        <div id="localVideoContainer" v-bind:class="{ 'local-video-overlay': ui.inFullscreen }" ></div>
        <network-graph-component ref="networkGraph" class="mb-3"></network-graph-component>


        <div id='videos' class="container">
            <div class="row justify-content-center">
            <div v-for="stream in peerStreams" :key="stream.id" class="col-md-6 col-sm-12 col-lg-4 col-ml-auto embed-responsive embed-responsive-4by3">
                <div class="">
                <video :srcObject.prop="stream"
                    v-on:webkitfullscreenchange="fullscreenVideo"
                    v-on:mozfullscreenchange="fullscreenVideo"
                    v-on:fullscreenchange="fullscreenVideo"
                    poster = "https://bevy.chat/img/logo_color.png"
                    controls="controls"
                    autoplay="autoplay"
                    class="embed-responsive-item"
                ></video>
                </div>
            </div>
            </div>
        </div>
    <!--
        <strong>Log</strong><br />
        <textarea id="logger" readonly class="form-control" rows=1></textarea>
        <hr />-->

        <!--<strong>Peer Connections</strong><br />
        <textarea id="connections" readonly class="form-control" rows=5></textarea>
        <hr />-->

    </div>
    <message-log-component v-bind:chatLog="chatLog" v-if="user.active"></message-log-component>

    <div class="flex-column" v-if="user.active">
        <div class="input-group">
            <input type="text" v-model="message" class="form-control" placeholder="Type a message" id="message" v-on:keyup.enter="sendMessage" />
            <div class="input-group-btn">
                <button class="btn btn-primary" type="button"  id="send" v-on:click="sendMessage">
                    <span class="sr-only">Send Message</span>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    </div>
</template>

<style scoped>
    #localVideoContainer {
        width:200px;
        float:right;
        z-index: 1;
    }
    #localVideoContainer >>> video {
        width:200px;
        position:fixed;
    }
    .local-video-overlay {
        position:fixed !important;
        top:10px  !important;
        right:10px !important;
        z-index:2147483646 !important;
    }

    button.local-video-overlay {
        z-index:2147483647 !important;
    }

    video[isFullscreen='true'] {
        position:fixed !important;
        background: #000;
        z-index: 1;
    }

    #videoToggle {
        float:right;
        position: relative;
        margin-left: -38px;
        width: 40px;
        border-radius: 0px;
        z-index:1;
    }

    #user-prompt {
        margin-top:10%;
    }
    .form-control-xl {
        font-size: 1.5em;
    }
    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s;
    }
    .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
        opacity: 0;
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
            connections: {},
            chatId: null,
            user: {active: false},
            stream: {enabled: false, connection: null, local:null},
            peerStreams: [],
            server: {ip:'bevy.chat', port:1337, signal: null},
            ui: {anonUsername: '', inFullscreen: false}
        }
    },
    methods: {
        fullscreenVideo(e) {
            var vm = this;
            //Don't actually fullscreen. Just make the video... Bigger
            if (document.fullscreenElement) {
                document.exitFullscreen();

                console.log("IS FULLSCREEN?");
                console.log(e.target.getAttribute('isFullscreen'));

                //Going fullscreen
                if(!vm.ui.inFullscreen) {
                    vm.ui.inFullscreen = true;
                    e.target.setAttribute('isFullscreen', true);
                } else {
                    vm.ui.inFullscreen = false;
                    //Closing fullscreen
                    e.target.setAttribute('isFullscreen', false);
                }
            }

            console.log("FULLSCREEN REQUEST");
            console.log(e);
        },
        setAnonUser(e) {
            var vm = this;
            if(vm.ui.anonUsername != '') {
                vm.user.name = vm.ui.anonUsername;
                vm.user.active = true;

                vm.init();
            }
        },
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
                if(Message.broadcast(vm.connections, vm.message)) {
                    //Write the message we just sent to ourself
                    vm.recieveMessage(vm.user.getDataObject(), vm.message, true);
                    vm.message = '';
                } else {
                    alert("Something went wrong!");
                }
            }
        },
        recieveMessage(user, data, self = false) {
            var vm = this;
            console.log("Recieved from: ");
            console.log(user);
            vm.chatLog.push({index: vm.chatLog.length, message: data, user: user, self: self});

            //var messageContainer = vm.$el.querySelector("#messages");
            //messageContainer.scrollTop = messageContainer.scrollHeight - 100;
        },
        outputConnections (cons) {
            var vm = this;

            var networkChartData = {nodes:[{id: 'me', name: 'Me'}], links:[]};

            //var txtConnections = document.getElementById('connections');
            //txtConnections.textContent = "(" + Object.keys(cons).length + ") open \n";

            for(var id in cons) {
                var peer = cons[id];
                var name = typeof peer.user != 'undefined' ? peer.user.name : 'X';

                //This is the host connection and it's actually bound to someone
                if(typeof peer != 'undefined' && peer.initiator && peer.clientid != null) {
                    //When you have the host connection
                    networkChartData.nodes.push({id: peer.clientid, name: name, status: 'client'}); //C For client
                    //networkChartData.nodes.push({id: host.clientid, name: host.clientid.substring(0,2)});
                    networkChartData.links.push({source: peer.clientid, target: 'me'});

                    //txtConnections.textContent += peer.id + " <--- " + peer.clientid + "\n";
                } else if(typeof peer != 'undefined' && peer.hostid != null) {

                    //When you're a client of a host
                    networkChartData.nodes.push({id: peer.hostid, name: name, status: 'host'}); //H for host
                    //networkChartData.nodes.push({id: id, name: id.substring(0,2)});
                    networkChartData.links.push({source: 'me', target: peer.hostid});

                    //txtConnections.textContent += peer.id + " ---> " + peer.hostid + "\n";
                }

            }

            vm.$refs.networkGraph.update(networkChartData);

        },
        /**
         * When a peer opens a stream, show the new connection
         */
        onPeerStream(stream) {
            var vm = this;
            vm.peerStreams.push(stream);

            /**
             * Fires twice. Once when the audio is removed and once when the video is removed
             */
            stream.onremovetrack = function(e) {
                //Find and remove this stream
                for(var i=0; i<vm.peerStreams.length; i++) {
                    //Already deleted. This event fires twice (once for video removal and once for audio removal)
                    if(typeof vm.peerStreams[i] == 'undefined' || typeof e.srcElement == 'undefined') {
                        continue;
                    }

                    //See if this is the video we want to delete
                    if(vm.peerStreams[i].id == e.srcElement.id) {
                        vm.peerStreams.splice(i, 1);
                        break;
                    }
                }
            };
        },
        /**
         * Fires when a new stream object has opened
         * Aka the user clicked the video button
         */
        onLocalStream(stream) {
            var vm = this;

            if(!vm.stream.enabled) {
                console.log("++++LOCAL STREAM ENABLED");
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
                console.log("Set stream vars and tell everyone to retry");
                vm.stream.enabled = true;
                vm.stream.connection = stream;

                //New Local stream! Send it off  to all the peers
                for(var id in vm.connections) {
                    if(vm.connections[id].connection == null
                        || !vm.connections[id].connection.connected
                        || vm.connections[id].connection.destroyed) {
                        console.log("SKIP CONNECTION " + id);
                        console.log(vm.connections[id]);
                        continue;
                    }

                    vm.connections[id].addStream(vm.stream.connection);

                    //console.log("+++++++++++++++++++Sending stream to " + id);
                    //console.log(vm.stream.connection);
                    //console.log(vm.stream.connection.getTracks());

                    //console.log(vm.connections[id]);
                    //vm.connections[id].send("CONNECT VIA " + id + " DAMNIT");
                    //vm.connections[id].removeStream(vm.stream.connection);
                    //vm.connections[id].connection.addStream(vm.stream.connection);
                    //var tracks = vm.stream.connection.getTracks();
                    //vm.connections[id].addTrack(tracks[0], stream);
                }
            }
        },
        sendStream(id) {
            var vm = this;

            if(typeof vm.connections[id] == 'undefined'
                || vm.connections[id].connection == null
                || !vm.connections[id].connection.connected
                || vm.connections[id].connection.destroyed) {
                console.log("Cannot send stream! BAD CONNECTION TO " + id);
                console.log(vm.connections[id]);
                return false;
            }

            //client.send("Sending stream to " + client._id);
            console.log("+Sending stream to " + id)
            //console.log(vm.stream.connection.getTracks());
            //console.log(client.streams);
            //client.removeStream(vm.stream.connection);

            if(vm.stream.enabled && !vm.connections[id].isStreaming) {
                console.log("+APPLYING STREAM");
                vm.connections[id].addStream(vm.stream.connection);
            }

        },
        stopLocalStream() {
            var vm = this;

            if(!vm.stream.enabled) {return}

            vm.stream.enabled = false;

            //Send this stream to all the peers
            for(var id in vm.connections) {
                if(!vm.connections[id].isStreaming) {
                    continue;
                }
                console.log("-----------------Remove stream from " + id);
                vm.connections[id].removeStream(vm.stream.connection);
            }

            //Also remove it from the UI
            var localStream = vm.stream.local.srcObject;
            var tracks = localStream.getTracks();

            tracks.forEach(function(track) {
                track.stop();
            });

            vm.stream.connection = null;
            vm.stream.local.srcObject = null;
            vm.$el.querySelector("#localVideoContainer").innerHTML = "";
        },
        init() {
            var vm = this;

            var Peer = require('simple-peer');
            var io = require('socket.io-client');

            vm.chatId = location.pathname.replace('/chat/', '');
            vm.server.signal = io.connect('https://' + vm.server.ip + ':' + vm.server.port);

            //var txtLogger = document.getElementById('logger');

            vm.server.signal.on('disconnect', function () {
                alert("Uh oh! You disconnected!");
                vm.connections = [];
            });

            vm.server.signal.on('connect', function () {
                console.log("Connected to signal server. Sending auth...");
                //Pass to the server that we want to join this chat room with this user
                //It will use the user to annouce to other connections who you are
                vm.server.signal.emit('join', {chatId: vm.chatId, user: vm.user.getAuthObject()});
            });

            /**
             * Fires when a new client connects. The new client create a new host peer
             * for every client in the mesh that it needs to connec tto
             */
            vm.server.signal.on('inithosts', function (numHosts) {
                console.log("init (" + numHosts + ") hosts");

                for(var i=0;i<numHosts;i++) {

                    var peer = new PeerConnection(vm.server.signal, true);
                    vm.connections[peer.id] = peer;

                    vm.connections[peer.id].connection.on('connect', function() {
                        vm.outputConnections(vm.connections);
                        if(vm.stream.enabled) {
                            console.log("Try and send a stream to " + peer.id);
                            vm.sendStream(peer.id);
                        }
                    });

                    vm.connections[peer.id].connection.on('close', function() {delete vm.connections[peer.id];});
                    vm.connections[peer.id].connection.on('error', function() {delete vm.connections[peer.id];});

                    vm.connections[peer.id].connection.on('data', function(data) {
                        vm.recieveMessage(vm.connections[peer.id].user, data);
                    });

                    vm.connections[peer.id].connection.on('stream', function(stream) {
                        vm.onPeerStream(stream);
                    });
                }
            });

            /**
             * Fires when the client has generated a WebRTC token and it needs to get back to the host
             */
            vm.server.signal.on('sendtohost', function (obj) {
                //Prevent signals to bad hosts that have closed
                if(typeof vm.connections[obj.hostid] != 'undefined' && !vm.connections[obj.hostid].connection.destroyed) {
                    console.log("host - binding returned client info");
                    vm.connections[obj.hostid].signal(obj.webRtcId);
                    vm.connections[obj.hostid].setUser(obj.user);
                    vm.connections[obj.hostid].setClientId(obj.clientid);
                } else {
                    console.log("UH OH");
                    delete vm.connections[obj.hostid];
                }
            });

            /**
             * Fires when a host is looking for clients to connect to
             * If there's no open client for this match host one will be created
             */
            vm.server.signal.on('initclient', function (obj) {
                console.log("Got request to init a client for host " + obj.hostid);
                var id=obj.hostid;

                //Key to the host id since it can possibly reqest to init a bunch of times during the handshake
                if(typeof vm.connections[obj.hostid] == 'undefined') {
                    console.log("Init a peer for host " + id);
                    var peer = new PeerConnection(vm.server.signal, false);

                    vm.connections[id] = peer;
                    vm.connections[id].setHostId(obj.hostid);
                    vm.connections[id].setUser(obj.user);

                    vm.connections[id].connection.on('connect', function() {
                        vm.outputConnections(vm.connections);
                        if(vm.stream.enabled) {
                            console.log("Try and send a client stream to " + id);
                            vm.sendStream(id);
                        }
                    });

                    vm.connections[id].connection.on('close', function() {delete vm.connections[id];});
                    vm.connections[id].connection.on('error', function() {delete vm.connections[id];});

                    vm.connections[id].connection.on('data', function(data) {
                        vm.recieveMessage(vm.connections[id].user, data);
                    });

                    vm.connections[id].connection.on('stream', stream => {vm.onPeerStream(stream); });
                }
                //Use the remote host id so that the client is overridden if it re-signals


                console.log("Signal host (" + obj.hostid + ") connection to client");
                vm.connections[id].signal(obj.webRtcId);


            });

        }
    },
mounted() {
    console.log('Component mounted.');
    //View model reference for inside scoped functions
    var vm = this;

    vm.user = new User();
    vm.user.auth().then(function(response) {
        //Prompt for a name
        if(response.success) {
            vm.init();
        }
    });
}
}

class PeerConnection {

    constructor(server, initiator) {
        var Peer = require('simple-peer');
        var self = this;

        self.connection = new Peer({
            initiator: initiator,
            config: {
                iceServers: [
                    {urls: 'stun:stun.services.mozilla.com'},
                    {urls: 'stun:stun.l.google.com:19302'},
                    {urls: 'turn:bevy.chat', username: 'bevychat', credential: 'bevychatturntest'}
                ]
            }
        });
        self.server = server;

        self.id = self.connection._id;
        self.user = {name: "anonymous", verified: false};

        self.initiator = initiator;
        self.hostid = initiator ? self.id : null;
        self.clientid = initiator ? null : self.id;

        self.isStreaming = false;

        self.connection.on('connect', function() {
            console.log("~~~~~Connected!~~~~~");
        });

        self.connection.on('signal', function (webRtcId) {
            if(self.connection.initiator) {
                console.log('Got initiator signal, sending off to client');
                self.server.emit('sendtoclient', {webRtcId: webRtcId, hostid: self.hostid, clientid: self.clientid});
            } else {
                console.log('Got client signal, sending off to host');

                //Got a response from the initiator
                self.server.emit('sendtohost', {webRtcId:webRtcId, hostid: self.hostid, clientid: self.clientid});
            }


        });

        self.connection.on('close', function() {
            console.log("Connection closed - " + self.id);
            self.destroy();
        });

        self.connection.on('error', function(err) {
            console.log("Connection error - " + self.id);
            self.destroy();
        });

        return this;
    }

    addStream(stream) {
        if(this.isStreaming) {
            console.log("ALREADY STREAMING");
        } else {
            console.log(this.connection);
            this.connection.addStream(stream);
            this.isStreaming = true;
        }

    }

    removeStream(stream) {
        if(!this.isStreaming) {
            console.log("NOT STREAMING");
        } else {
            this.connection.removeStream(stream);
            this.isStreaming = false;
        }

    }

    setHostId(id) {
        this.hostid = id;
    }
    setClientId(id) {
        this.clientid = id;
    }
    setUser(user) {
        this.user = user;
    }

    signal(webRtcId) {
        this.connection.signal(webRtcId);
    }

    destroy() {
        this.connection.destroy();
        return null;
    }
}

class User {
    //Gets the current authenticated user
    constructor() {
        var self = this;
        var id, name, email, avatar, token, verified, active;

        this.transport = axios.create({
            withCredentials: true
        });

        //Used to determine if the user object has been instantiated
        self.active = false;
    }

    auth() {
        var self = this;

        //Get this chat database record
        return this.transport.get('/api/1.0/users/whoami').then(response => {
            //console.log(response.data);
            self.id = response.data.data.id;
            self.name = response.data.data.name;
            self.email = response.data.data.email;
            self.token = response.data.data.token;
            self.verified = true;
            self.active = true;
            return response.data;
        }).catch(error => {
            if (error.response.status === 401) {
                //Prop up an empty user data object
                return {
                    success: false,
                    message: '',
                    data: {
                        id: null,
                        name: self.name,
                        verified: false
                    }
                }
            }
        });
    }

    getDataObject() {
        return {
            id: this.id,
            name: this.name,
            verified: this.verified
        };
    }

    getAuthObject() {
        return {
            name: this.name,
            token: this.token
        };
    }
}

class Message {
    constructor() {}

    static broadcast(connections, message) {
        if(message == '' || Object.keys(connections).length == 0) {
            return false;
        }

        console.log("Broadcasting to (" + Object.keys(connections).length + ") open connections");

        for(var id in connections) {
            var conn = connections[id].connection;
            if(conn == null || !conn.connected || conn.destroyed) {
                console.log("Tried sending through bad connection id " + id);
                console.log(connections);
                console.log(conn);
                console.log("Connected " + conn.connected);
                console.log("Destroyed " + conn.destroyed);
                delete connections[id];
                continue;
            }

            console.log("Sending to " + id);
            conn.send(message);
        }

        //After deleting any bad connections, if there's any left that we sent to then return true
        return Object.keys(connections).length > 0;


    }
}





</script>