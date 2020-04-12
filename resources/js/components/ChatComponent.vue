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
    <div class="chat-container pl-5 pr-5 flex-row"  v-if="user.active">
        <!--Local Video Button-->
        <button
            class="btn btn-primary float-right"
            type="button"
            id="btn-local-video-toggle"
            v-bind:class="{
                'btn-off': !stream.videoenabled,
                'local-video-overlay': ui.inFullscreen
            }"
            v-on:click="toggleVideo">
            <span class="sr-only" v-if="!stream.videoenabled">Start Video</span>
            <i class="fas fa-video-slash" v-if="!stream.videoenabled"></i>

            <span class="sr-only" v-if="stream.videoenabled">Stop Video</span>
            <i class="fas fa-video" v-if="stream.videoenabled"></i>
        </button>

        <!--Local Audio Button-->
        <button class="btn btn-danger float-right"
            type="button"
            id="btn-local-audio-toggle"
            v-bind:class="{
                'btn-off': !stream.audioenabled,
                'local-audio-overlay': ui.inFullscreen
            }"
            v-if="stream.videoenabled || stream.screenshareenabled"
            v-on:click="toggleAudio">
            <span class="sr-only" v-if="!stream.audioenabled">Enable Audio</span>
            <i class="fas fa-microphone-slash" v-if="!stream.audioenabled"></i>

            <span class="sr-only" v-if="stream.audioenabled">Mute Video</span>
            <i class="fas fa-microphone" v-if="stream.audioenabled"></i>
        </button>

        <!--Local Screenshare Button-->
        <button class="btn btn-success float-right"
            type="button"
            id="btn-local-screenshare-toggle"
            v-bind:class="{
                'btn-off': !stream.screenshareenabled,
                'local-screenshare-overlay': ui.inFullscreen
            }"
            v-if="stream.videoenabled || stream.screenshareenabled"
            v-on:click="toggleScreenshare">
            <span class="sr-only" v-if="!stream.screenshareenabled">Enable Screenshare</span>
            <i class="fas fa-desktop" v-if="!stream.screenshareenabled"></i>
            <i class="fas fa-slash" v-if="!stream.screenshareenabled"></i>

            <span class="sr-only" v-if="stream.screenshareenabled">Stop Sharing</span>
            <i class="fas fa-desktop" v-if="stream.screenshareenabled"></i>
        </button>

        <!--Switch Video Feed Button-->
        <button class="btn btn-primary float-right"
            type="button"
            id="btn-local-swapvideo-toggle"
            v-bind:class="{
                'local-swapvideo-overlay': ui.inFullscreen
            }"
            v-if="stream.videoenabled && user.devices.video.length > 1"
            v-on:click="swapVideoFeed">

            <span class="sr-only">Switch Video</span>
            <i class="fas fa-sync-alt"></i>
        </button>

        <div id="local-video-container"
            v-bind:class="{
                        'local-video-overlay': ui.inFullscreen,
                        'local-video-sm': stream.localsize =='sm',
                        'local-video-md': stream.localsize =='md',
                        'local-video-lg': stream.localsize =='lg'
                    }"
            >

            <video :srcObject.prop="stream.connection"
                    v-on:click="adjustLocalVideoSize"
                    poster = "https://bevy.chat/img/logo_color.png"
                    autoplay="autoplay"
                    muted="muted"
                    class="local-stream"
                    v-bind="stream.local"
                    v-if="stream.videoenabled || stream.screenshareenabled"
                ></video>




        </div>

        <network-graph-component ref="networkGraph" class="mb-3"></network-graph-component>


        <div id='videos' class="container">
            <div class="row justify-content-center">
            <div v-for="stream in peerStreams" :key="stream.id" class="col-md-6 col-sm-12 col-lg-4 col-ml-auto embed-responsive embed-responsive-4by3">
                <div
                    class="peer-video-details"
                    v-bind:class="{ 'peer-video-fullscreen': ui.inFullscreen }">
                    {{stream.peeruser.name}}
                    <i class="fas fa-lock" v-if="stream.peeruser.verified"></i>
                </div>
                <video :srcObject.prop="stream"
                    v-on:webkitfullscreenchange="fullscreenVideo"
                    v-on:mozfullscreenchange="fullscreenVideo"
                    v-on:fullscreenchange="fullscreenVideo"
                    v-on:play="setDefaultVolume"
                    poster = "https://bevy.chat/img/logo_color.png"
                    autoplay="autoplay"
                    controls="controls"
                    volume="1"
                    class="embed-responsive-item"
                    v-bind:class="{ 'peer-video-fullscreen': ui.inFullscreen }"
                ></video>
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
    #btn-local-video-toggle {
        right:10px;
        border-radius: 2em !important;
        width: 4em;
        height: 4em;
        position: fixed;
        z-index:2147483647;
        margin-top:1em;
    }

    #btn-local-audio-toggle {
        right:10px;
        border-radius: 2em !important;
        width: 4em;
        height: 4em;
        position: fixed;
        z-index:2147483647;
        margin-top:6em;
    }

    #btn-local-screenshare-toggle {
        right:10px;
        border-radius: 2em !important;
        width: 4em;
        height: 4em;
        position: fixed;
        z-index:2147483647;
        margin-top:11em;
    }

    #btn-local-swapvideo-toggle {
        right:10px;
        border-radius: 2em !important;
        width: 4em;
        height: 4em;
        position: fixed;
        z-index:2147483647;
        margin-top:16em;
    }

    .btn-off {
        opacity: 0.75;
    }
    /**Adjust the slash since font awesome doesn't offer a video slash option */
    #btn-local-screenshare-toggle >>> .fa-slash {
        display:block;
        margin-top:-20px;
    }

    #local-video-container.local-video-sm,
    #local-video-container.local-video-sm >>> video {
        margin-right:25px;
        width:100px;
    }
    #local-video-container.local-video-md,
    #local-video-container.local-video-md >>> video {
        width:200px;
    }
    #local-video-container.local-video-lg,
    #local-video-container.local-video-lg >>> video {
        width:300px;
    }
    #local-video-container, #local-video-container >>> video {

        margin-top:20px;
        position:fixed;
        right:2em;
        border-radius:3px;
        z-index: 2147483646;
    }
    .peer-video-details {
        position: absolute;
        z-index: 2;
        display: block;
        top: 0px;
        left: 0px;
        float: left;
        color: #fff;
        background: #000;
        opacity: 0.5;
        padding-right: 5px;
        padding-left: 5px;
    }
    /* When fullscreened, shift things around*/
    #local-video-container.local-video-overlay,
    #local-video-container.local-video-overlay >>> video,
    #btn-local-video-toggle.local-video-overlay {
        margin-top:0px;
        top:0px;
        right:0px;
    }

    button.local-video-overlay,
    button.local-audio-overlay,
    button.local-screenshare-overlay,
    button.local-swapvideo-overlay {
        margin-top:0px !important;
        right:0px !important;
        z-index:2147483647 !important;
    }
    button.local-video-overlay {
        top:0px;
    }
    button.local-audio-overlay {
        top:5em !important;
    }
    button.local-screenshare-overlay {
        top:10em !important;
    }
    button.local-swapvideo-overlay {
        top:15em !important;
    }


    /* Main Video Fullscreen */
    video.peer-video-fullscreen {
        position:fixed !important;
        background: #000;
        z-index: 1;
    }
    .peer-video-details.peer-video-fullscreen {
        position:fixed;
    }

    #user-prompt {
        margin-top:10%;
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
            stream: {videoenabled: false, audioenabled:true, screenshareenabled: false, connection: null, local:null, localsize:'lg'},
            peerStreams: [],
            server: {ip:'bevy.chat', port:1337, signal: null},
            ui: {anonUsername: '', inFullscreen: false, sound: {connect: null, disconnect: null, message: null}}
        }
    },
    methods: {
        setDefaultVolume(e) {
            e.target.volume = 1;
        },
        swapVideoFeed(e) {
            var vm = this;

            var activeId = vm.user.devices.active.video;

            for(var i=0; i<vm.user.devices.video.length; i++) {
                if(vm.user.devices.video[i].deviceId == activeId) {
                    //We found the current active one. Get the next
                    if(i < vm.user.devices.video.length-1) {
                        vm.user.devices.active.video = vm.user.devices.video[i+1].deviceId;
                    } else {
                        vm.user.devices.active.video = vm.user.devices.video[0].deviceId;
                    }
                    break;
                }
            }

            vm.stopLocalStream();
            vm.stream.videoenabled = false;
            vm.toggleVideo({'message': "swapping video feed to another camera"});
        },
        toggleScreenshare(e) {
            var vm = this;
            var options = options = {video: {cursor: "always"}};

            if(vm.stream.videoenabled && !vm.stream.screenshareenabled) {
                navigator.mediaDevices.getDisplayMedia(options).then(function(stream) {
                    vm.stopLocalStream();
                    vm.stream.videoenabled = false;
                    vm.stream.screenshareenabled = true;
                    vm.stream.connection = stream;
                    vm.onLocalStream(stream);

                }).catch((e) => {
                    vm.stream.screenshareenabled = false;
                    console.log("Local Screenshare Stream Error!");
                    console.log(e);
                    //vm.toggleVideo({'message': "toggling back to local video from screenshare"});
                });
            } else if(vm.stream.screenshareenabled) {
                console.log("Turning screenshare off");
                vm.stopLocalStream();
                vm.stream.screenshareenabled = false;
                vm.toggleVideo({'message': "toggling back to local video from screenshare"});
            }
        },
        adjustLocalVideoSize(e) {
            var vm = this;
            if(vm.stream.localsize == 'lg') {
                vm.stream.localsize = 'md'
            } else if(vm.stream.localsize == 'md') {
                vm.stream.localsize = 'sm'
            } else if(vm.stream.localsize == 'sm') {
                vm.stream.localsize = 'lg'
            }
        },
        fullscreenVideo(e) {
            var vm = this;
            //Don't actually fullscreen. Just make the video... Bigger
            if (document.fullscreenElement) {
                document.exitFullscreen();

                //Going fullscreen
                if(!vm.ui.inFullscreen) {
                    vm.ui.inFullscreen = true;
                } else {
                    vm.ui.inFullscreen = false;
                }
            }
        },
        setAnonUser(e) {
            var vm = this;
            if(vm.ui.anonUsername != '') {
                vm.user.name = vm.ui.anonUsername;
                vm.user.active = true;

                vm.init();
            }
        },
        toggleAudio(e) {
            var vm = this;
            if((vm.stream.videoenabled || vm.stream.screenshareenabled) && vm.stream.audioenabled) {
                vm.stream.connection.getAudioTracks().forEach(function(track){track.enabled = false;});
                vm.stream.audioenabled = false;
            } else if((vm.stream.videoenabled || vm.stream.screenshareenabled) && !vm.stream.audioenabled) {
                vm.stream.connection.getAudioTracks().forEach(function(track){track.enabled = true;});
                vm.stream.audioenabled = true;
            }
        },
        toggleVideo(e) {
            var vm = this;

            vm.user.discoverDevices(function() {
                //Turn off screensharing and swap back to video
                if(!vm.stream.videoenabled && vm.stream.screenshareenabled) {
                    vm.stopLocalStream();
                    vm.stream.screenshareenabled = false;
                }

                if(!vm.stream.videoenabled) {
                    console.log("Turning video on with camera id " + vm.user.devices.active.video);
                    var options = {
                            video: true,
                            audio: true
                        };
                    if(vm.user.devices.active.video != null) {
                       // options.video = {deviceId: { exact: vm.user.devices.active.video }};
                    }


                    navigator.mediaDevices.getUserMedia(options).then(function(stream) {
                        vm.stream.videoenabled = true;
                        vm.stream.screenshareenabled = false;
                        vm.onLocalStream(stream);
                    }).catch((e) => {
                        console.log("Local Video Stream Error!");
                        console.log(e);
                    });
                } else {
                    vm.stopLocalStream();
                    vm.stream.videoenabled = false;
                }
            });
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
            vm.chatLog.push({index: vm.chatLog.length, message: data, user: user, self: self});
        },
        outputConnections () {
            var vm = this;

            var networkChartData = {nodes:[{id: 'me', name: 'Me'}], links:[]};

            //var txtConnections = document.getElementById('connections');
            //txtConnections.textContent = "(" + Object.keys(cons).length + ") open \n";

            for(var id in vm.connections) {
                var peer = vm.connections[id];
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
        onPeerStream(stream, peerid) {
            var vm = this;

            //Check for duplicates incase buttons are spammed
            for(var i=0; i < vm.peerStreams.length; i++) {
                //Duplicate stream! Ignore it
                if(vm.peerStreams[i].id == stream.id) {
                    return;
                }
            }

            stream.peerid = peerid;
            stream.peeruser = vm.connections[peerid].user;


            vm.peerStreams.push(stream);

            stream.inactive = function(e) {
                console.log("ON INACTIVE");
                console.log(e);
            };

            stream.onended = function(e) {
                console.log("ON ENDED");
                console.log(e);
            };

            stream.addEventListener('ended', function(e) {
                console.log("ON INACTIVE");
                console.log(e);
            });

            /**
             * Fires twice. Once when the audio is removed and once when the video is removed
             */
            stream.onremovetrack = function(e) {
                console.log("ON REMOVE TRACK!");
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

            console.log("++++LOCAL STREAM ENABLED");
            /*var localVideoContainer = vm.$el.querySelector("#local-video-container");
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
            vm.stream.local = video;*/
            console.log("Set stream vars and tell everyone to retry");
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

            if(vm.stream.videoenabled && !vm.connections[id].isStreaming) {
                console.log("+APPLYING STREAM");
                vm.connections[id].addStream(vm.stream.connection);
            }

        },
        stopLocalStream() {
            var vm = this;

            if(!vm.stream.videoenabled && !vm.stream.screenshareenabled) {return}

            //Remove this stream to all the peers so they don't need to do the timeout removal
            for(var id in vm.connections) {
                if(!vm.connections[id].isStreaming) {
                    continue;
                }
                vm.connections[id].removeStream(vm.stream.connection);
            }

            //Also remove it from the UI / Kill the feed
            var tracks = vm.stream.connection.getTracks();

            tracks.forEach(function(track) {
                track.stop();
            });

            vm.stream.connection = null;
        },
        handlePeerDisconnect(id) {
            var vm = this;
            vm.ui.sound.disconnect.play();
            delete vm.connections[id];
            vm.outputConnections();

            //Also remove anything they were streaming
            for(var i=0; i<vm.peerStreams.length; i++) {
                //See if this is the video we want to delete
                if(vm.peerStreams[i].peerid == id) {
                    console.log("Found a dead stream. Removing...");
                    vm.peerStreams.splice(i, 1);
                    break;
                }
            }
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
                        if(vm.ui.sound.connect.waitUntil <= Date.now()) {
                            vm.ui.sound.connect.play();
                            vm.ui.sound.connect.waitUntil = Date.now() + 5000; //Wait 5 seconds before playing again
                        }

                        vm.outputConnections();

                        if(vm.stream.videoenabled) {
                            console.log("Try and send a stream to " + peer.id);
                            vm.sendStream(peer.id);
                        }
                    });

                    vm.connections[peer.id].connection.on('close', function() {vm.handlePeerDisconnect(peer.id);});
                    vm.connections[peer.id].connection.on('error', function() {vm.handlePeerDisconnect(peer.id);});

                    vm.connections[peer.id].connection.on('data', function(data) {
                        vm.ui.sound.message.play();
                        vm.recieveMessage(vm.connections[peer.id].user, data);
                    });

                    vm.connections[peer.id].connection.on('stream', function(stream) {
                        console.log("ON PEER STREAM");
                        vm.onPeerStream(stream, peer.id);
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
                        if(vm.ui.sound.connect.waitUntil <= Date.now()) {
                            vm.ui.sound.connect.play();
                            vm.ui.sound.connect.waitUntil = Date.now() + 5000; //Wait 5 seconds before playing again
                        }

                        vm.outputConnections();
                        if(vm.stream.videoenabled) {
                            console.log("Try and send a client stream to " + id);
                            vm.sendStream(id);
                        }
                    });

                    vm.connections[id].connection.on('close', function() {vm.handlePeerDisconnect(id);});
                    vm.connections[id].connection.on('error', function() {vm.handlePeerDisconnect(id);});

                    vm.connections[id].connection.on('data', function(data) {
                        vm.ui.sound.message.play();
                        vm.recieveMessage(vm.connections[id].user, data);
                    });

                    vm.connections[id].connection.on('stream', stream => {vm.onPeerStream(stream, id); });
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

    vm.ui.sound.connect = new Audio("/media/join.mp3");
    vm.ui.sound.connect.waitUntil = Date.now();

    vm.ui.sound.disconnect = new Audio("/media/leave.mp3");
    vm.ui.sound.disconnect.waitUntil = Date.now();

    vm.ui.sound.message = new Audio("/media/message.mp3");
    vm.ui.sound.message.waitUntil = Date.now();

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
                    {urls: 'stun:bevy.chat'},
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
        self.id
        self.name
        self.email
        self.avatar
        self.token
        self.verified
        self.active;
        self.devices = {video: [], audio: [], active: {video: null, audio: null}};

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

    getVideoDevices() {
        return this.devices.video;
    }

    getAudioDevices() {
        return this.devices.audio;
    }

    discoverDevices(cb) {
        var self = this;

        //If we've already found a video AND audio device, don't bother searching again
        if(self.devices.video.length == 0 || self.devices.audio.length == 0) {
            console.log("Discovering input devices...");
            navigator.mediaDevices.enumerateDevices().then(function(devices) {
                for(var i=0; i<devices.length; i++) {
                    if(devices[i].kind == "audioinput") {
                        if(self.devices.audio.length == 0) {
                            //Set this device to be the default
                            //self.devices.active.audio = devices[i].deviceId;
                        }
                        self.devices.audio.push(devices[i]);
                    } else if(devices[i].kind == "videoinput") {
                        if(self.devices.video.length == 0) {
                            //Set this device to be the default
                            //self.devices.active.video = devices[i].deviceId;
                        }
                        self.devices.video.push(devices[i]);
                    }
                }

                cb();
            });
        }

        cb();
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