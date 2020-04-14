<template>
    <div class="d-flex flex-column w-100">
    <div ref="modalcontainer"></div>
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
        <button class="btn-leave-chat btn btn-outline-danger" v-on:click="confirmLeave">
            <i class="fas fa-sign-out-alt"></i>
            <span class="sr-only">Leave Chat</span>
        </button>
        <!--Local Video Button-->
        <button
            class="btn btn-primary float-right"
            type="button"
            id="btn-local-video-toggle"
            v-bind:class="{
                'btn-off': !stream.videoenabled,
                'local-video-overlay': ui.inFullscreen
            }"
            v-on:click="toggleVideo"
            v-if="ui.videoenabled">
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

        <div id="local-video-container" v-draggable v-on:draggable-onclick="adjustLocalVideoSize"
            v-bind:class="{
                        'local-video-overlay': ui.inFullscreen,
                        'local-video-sm': stream.localsize =='sm',
                        'local-video-md': stream.localsize =='md',
                        'local-video-lg': stream.localsize =='lg'
                    }"
            >

            <video :srcObject.prop="stream.connection"

                    poster = "https://bevy.chat/img/video_poster.png"
                    autoplay="autoplay"
                    muted="muted"
                    class="local-stream"
                    v-bind="stream.local"
                    v-if="stream.videoenabled || stream.screenshareenabled"
                ></video>




        </div>

        <network-graph-component ref="networkGraph" class="mb-3"></network-graph-component>


        <div id="videos" class="container">
            <div class="row justify-content-center video-connections">
            <div v-if="peerStreams.length == 0" class="no-video-connections">
                <h1><i class="fas fa-broadcast-tower"></i><i class="fas fa-slash tower-slash"></i></h1>
                <p>Nobody is streaming</p>
            </div>
            <div v-for="stream in peerStreams"
                :key="stream.id"
                class="col-md-6 col-sm-12 col-lg-4 col-ml-auto embed-responsive embed-responsive-4by3">
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
                    v-on:click="onDoubleClickCheck"
                    v-on:play="setDefaultVolume"

                    poster = "https://bevy.chat/img/video_poster.png"
                    autoplay="autoplay"
                    volume="1"
                    class="embed-responsive-item remote-stream"
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
    .no-video-connections {
        padding: 4vh;
        text-align: center;
    }
    .btn-leave-chat {
        position: fixed;
        width: 30%;
        top: 0px;
        left: 50%;
        margin-top: 6px;
        margin-left: -15%;
    }
    .video-connections {
        background: #eee;
        color:#555;
        padding: 1vh;
        border-radius: 5px;
        box-shadow: 0px 1px 3px #ccc;
    }
    .no-video-connections >>> h1 {
        height:2em;
    }
    .no-video-connections >>> i {
        position: absolute;
        /*Center the icons*/
        left: 0;
        right: 0;
    }


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

    .remote-stream {
        background:#000;
    }

    /*Remove any previous positions*/
    .is-draggable {
        top:unset;
        bottom: unset;
        right:unset;
        left:unset;
    }

    video {
        border-radius: 5px;
        box-shadow: 0px 1px 3px #000;
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
    #local-video-container {
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
    #local-video-container.local-video-overlay >>> video {
        margin-right:0px;
        bottom:0px;
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

import SoundEffect from '../models/SoundEffect.js';
import User from '../models/User.js';
import Message from '../models/Message.js';
import PeerConnection from '../models/PeerConnection.js';
import ModalComponent from './ModalComponent.vue'

export default {
    data: function () {
        return {
            message: '',
            chatLog: [],
            connections: {},
            chatId: null,
            user: {active: false},
            stream: {videoenabled: false, audioenabled:true, screenshareenabled: false, connection: null, local:null, localsize:'md'},
            peerStreams: [],
            server: {ip:'bevy.chat', port:1337, signal: null},
            ui: {videoenabled: true, anonUsername: '', inFullscreen: false, dblClickTimer: null, sound: null}
        }
    },
    methods: {
        confirmLeave(e) {
            var vm = this;
            console.log("Confirm leave");
            const Confirmation = Vue.extend(ModalComponent);
            var modal = new Confirmation({propsData: {
                    close: {text: "Go back"},
                    confirm: {
                        text: "Leave",
                        class: "btn btn-danger"
                    }}
            });

            /*var modal = Vue.extend(ModalComponent)
            const modal = new Vue( Object.assign({}, ModalComponent, {
                propsData: {
                    confirm: {
                        text: "leave",
                        class: "btn btn-danger"
                    }}
            }));*/

            modal.$slots.header = ['Confirm'];
            modal.$slots.body = ['Are you sure you want to leave this chat?'];
            modal.$mount();
            vm.$refs.modalcontainer.appendChild(modal.$el)

            modal.$on('close', function(e) {
                modal.$destroy();
                modal.$el.remove();
            });

            modal.$on('confirm', function(e) {
                modal.$destroy();
                modal.$el.remove();

                //Let everyone know I'm leaving so they don't sit then hanging
                for(var id in vm.connections) {
                    //If we're streaming to them then kill it
                    vm.connections[id].removeStream(vm.stream.connection);
                    vm.connections[id].destroy();
                }
                //vm.server.signal.close();
                //Go back to the homepage
                window.location.href = '/';
            });
        },
        onDoubleClickCheck(e) {
            var vm = this;

            //Play the video if you touched it
            //Chrome disables auto-play if you don't interact with the document first
            e.target.play();

            //Clicked again within 1s, trigger fullscreen
            if(vm.ui.dblClickTimer != null && Date.now() - 1000 <= vm.ui.dblClickTimer) {
                vm.ui.dblClickTimer = null;
                vm.fullscreenVideo(e);
            } else {
                vm.ui.dblClickTimer = Date.now();
            }
        },
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

            //Don't resize if we're still in the dragging lifecycle
            if(e.target.hasAttribute('is-dragging')
                || e.target.parentElement.hasAttribute('is-dragging')) {
                return;
            }

            if(vm.stream.localsize == 'lg') {
                vm.stream.localsize = 'md'
            } else if(vm.stream.localsize == 'md') {
                vm.stream.localsize = 'sm'
            } else if(vm.stream.localsize == 'sm') {
                vm.stream.localsize = 'md'
            }

            //Don't lose the element off the top/left screen
            if(e.target.offsetTop < 0) {
                e.target.style.top = "0px";
            }
            if(e.target.offsetLeft < 0) {
                e.target.style.left = "0px";
            }

            //Don't lose the element off the bottom/right screen
            if(e.target.offsetTop > (window.screen.height - 200)) {
                e.target.style.top = (window.screen.height - 200) + "px";
            }
            if(e.target.offsetLeft > (window.screen.width - 100)) {
                e.target.style.left = (window.screen.width - 100) + "px";
            }
        },
        fullscreenVideo(e) {
            var vm = this;
            //Don't actually fullscreen. Just make the video... Bigger
            if (document.fullscreenElement) {
                document.exitFullscreen();
            }

            //Going fullscreen
            if(!vm.ui.inFullscreen) {
                vm.ui.inFullscreen = true;
            } else {
                vm.ui.inFullscreen = false;
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
                console.log("Muted");
                vm.stream.audioenabled = false;
            } else if((vm.stream.videoenabled || vm.stream.screenshareenabled) && !vm.stream.audioenabled) {
                vm.stream.connection.getAudioTracks().forEach(function(track){track.enabled = true;});
                console.log("Not Muted");
                vm.stream.audioenabled = true;
            }
        },
        toggleVideo(e) {
            var vm = this;

            if(typeof navigator.mediaDevices == 'undefined') {
                alert("Something went wrong and your device does not support video");
                return;
            }

            vm.user.discoverDevices(function() {
                //Turn off screensharing and swap back to video
                if(!vm.stream.videoenabled && vm.stream.screenshareenabled) {
                    vm.stopLocalStream();
                    vm.stream.screenshareenabled = false;
                }

                if(!vm.stream.videoenabled) {
                    console.log("Turning on camera...");
                    var options = {
                            video: true,
                            audio: true
                        };
                    if(vm.user.devices.active.video != null) {
                        console.log("Turning video on with camera id " + vm.user.devices.active.video);
                        options.video = {deviceId: { ideal: vm.user.devices.active.video }};
                    }
                    try {
                        navigator.mediaDevices.getUserMedia(options).then(function(stream) {
                            vm.stream.videoenabled = true;
                            vm.stream.screenshareenabled = false;
                            vm.onLocalStream(stream);
                        }).catch((e) => {
                            alert("Something went horrible wrong when getting your video feed.\nYou should probably screencap this and send it to Jake\n\n\nError: " + JSON.stringify(e) + "\n\nOptions: " + JSON.stringify(options));
                            console.log("Local Video Stream Error!");
                            console.log(e);
                        });
                    } catch (e) {
                        console.log("Could not get user media for local stream");
                        console.log(e);
                    }
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

            console.log("Local stream created - Set stream vars and tell everyone to retry");
            vm.stream.connection = stream;

            //New Local stream! Send it off  to all the peers
            for(var id in vm.connections) {
                if(vm.connections[id].connection == null
                    || !vm.connections[id].connection.connected
                    || vm.connections[id].connection.destroyed) {
                    console.log("Don't send stream. Skip bad connection " + id);
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
            vm.ui.sound.play('disconnect');
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
                    var id = peer.id;
                    vm.connections[id] = peer;
                    console.log("NEW HOST PEER " + id);
                    vm.connections[id].connection.on('connect', function() {
                        vm.ui.sound.play('connect');

                        vm.outputConnections();

                        if(vm.stream.videoenabled) {
                            console.log("Try and send a stream to " + this._id);
                            vm.sendStream(this._id);
                        }
                    });

                    vm.connections[id].connection.on('close', function() {vm.handlePeerDisconnect(this._id);});
                    vm.connections[id].connection.on('error', function() {vm.handlePeerDisconnect(this._id);});

                    vm.connections[id].connection.on('data', function(data) {
                        vm.ui.sound.play('message');
                        vm.recieveMessage(vm.connections[this._id].user, data);
                    });

                    vm.connections[id].connection.on('stream', function(stream) {
                        console.log("ON PEER STREAM");
                        vm.onPeerStream(stream, this._id);
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
                if(typeof vm.connections[id] == 'undefined') {
                    console.log("Init a peer for host " + id);
                    var peer = new PeerConnection(vm.server.signal, false);

                    vm.connections[id] = peer;
                    vm.connections[id].setHostId(obj.hostid);
                    vm.connections[id].setUser(obj.user);

                    vm.connections[id].connection.on('connect', function() {
                        console.log("CONNECTED TO CLIENT~~");
                        console.log(this);
                        console.log(vm.connections[id].user);
                        vm.ui.sound.play('connect');

                        vm.outputConnections();
                        if(vm.stream.videoenabled) {
                            console.log("Try and send a client stream to " + id);
                            vm.sendStream(id);
                        }
                    });

                    vm.connections[id].connection.on('close', function() {vm.handlePeerDisconnect(id);});
                    vm.connections[id].connection.on('error', function() {vm.handlePeerDisconnect(id);});

                    vm.connections[id].connection.on('data', function(data) {
                        vm.ui.sound.play('message');
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
    console.log('Chat Component mounted.');
    //View model reference for inside scoped functions
    var vm = this;
    vm.ui.sound = new SoundEffect();
    //Hide the video button since they don't support mediaDevices
    vm.ui.videoenabled = typeof navigator.mediaDevices != 'undefined';

    vm.user = new User();
    vm.user.auth().then(function(response) {
        //Prompt for a name
        if(response.success) {
            vm.init();
        }
    });
}
}








</script>