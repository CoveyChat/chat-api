<template>
    <div class="d-flex flex-column w-100">
    <div ref="modalcontainer"></div>
    <div class="peer-video-rebinding-wait-container">
        <div  class="peer-video-rebinding-wait text-center"
            v-if="(ui.fullscreen.target !== null && ui.fullscreen.wait)">
            <h1><i class="fas fa-video-slash"></i></h1>
            <span class="sr-only">Connection Lost</span>
        </div>
    </div>
    <div id="user-prompt" v-if="!user.active" name="fade" class="flex-row flex-grow-1">
        <div class="row">
            <div class="col-sm-12 offset-md-3 col-md-6 offset-lg-4 col-lg-4 ">
                <div class="card card-body p-5 text-center">
                    <h1>{{chatName}}</h1>
                    <br />
                    <p>
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
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="chat-container flex-row flex-fill"
        v-bind:class="{ 'peer-video-fullscreen': ui.fullscreen.active }"
        v-if="user.active">
        <button class="btn-leave-chat btn btn-danger" v-on:click="confirmLeave">
            <i class="fas fa-sign-out-alt"></i>
            <span class="sr-only">Leave Chat</span>
        </button>





        <controls-component
            v-bind:inFullscreen="ui.fullscreen.active"
            v-bind:deviceAccess="ui.deviceAccess"
            v-bind:screenshareAccess="ui.screenshareAccess"
            v-bind:videoEnabled="stream.videoenabled"
            v-bind:audioEnabled="stream.audioenabled"
            v-bind:screenshareEnabled="stream.screenshareenabled"
            v-bind:videoDevices="user.devices.video"

            v-on:toggleVideo="toggleVideo"
            v-on:toggleAudio="toggleAudio"
            v-on:toggleScreenshare="toggleScreenshare"
            v-on:swapVideoFeed="swapVideoFeed"
            v-on:changeSettings="changeSettings"
        >
        </controls-component>






        <div id="local-video-container" v-draggable v-on:draggable-onclick="adjustLocalVideoSize"
            v-bind:class="{
                        'local-video-overlay': ui.fullscreen.active,
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

        <network-graph-component
            ref="networkGraph"
            class="mb-3"
            v-bind:inFullscreen="ui.fullscreen.active">
        </network-graph-component>

        <div id="peer-video-container" class="container-fluid" v-bind:class="{ 'peer-video-fullscreen': ui.fullscreen.active }">
            <div class="row justify-content-center video-connections flex-fill">
            <div v-if="peerStreams.length == 0" class="no-video-connections">
                <h1><i class="fas fa-broadcast-tower"></i><i class="fas fa-slash tower-slash"></i></h1>
                <p>Nobody is streaming</p>
            </div>
            <div v-for="stream in peerStreams"
                :key="stream.id"
                class="col-sm-6 col-md-6 col-lg-4 col-ml-auto embed-responsive embed-responsive-4by3">

                <peer-video-component
                v-bind:stream="stream"
                v-bind:startFullscreen="stream.startFullscreen"
                v-on:openFullscreen=" ui.fullscreen.active = true; ui.fullscreen.target = stream.peerid;"
                v-on:closeFullscreen="ui.fullscreen.active = false; ui.fullscreen.target = null;"
                v-on:closeFullscreenOnDestroy="closeFullscreenOnDestroy">
                </peer-video-component>
            </div>
            </div>
        </div>
    </div>

    <div class="d-flex" v-if="ui.fullscreen.active">
        <button class="btn btn-md btn-primary btn-show-messages"
            v-bind:class="{'btn-off': !ui.showMessagesFullscreen}"
            v-on:click="ui.showMessagesFullscreen = !ui.showMessagesFullscreen">
            <span class="sr-only">Show messages Panel</span>
            <i class="fas fa-comment" v-if="!ui.showMessagesFullscreen"></i>
            <i class="fas fa-comment-slash" v-if="ui.showMessagesFullscreen"></i>
        </button>
    </div>

    <message-log-component
        v-bind:chatLog="chatLog" v-if="user.active"
        v-bind:inFullscreen="ui.fullscreen.active"
        v-bind:showMessagesFullscreen="ui.showMessagesFullscreen">
    </message-log-component>

    <div class="flex-column message-box"
        v-if="user.active"
        v-bind:class="{
            'peer-video-fullscreen': (ui.fullscreen.active && ui.showMessagesFullscreen),
            'chat-disabled': (Object.keys(connections).length == 0)}">
        <div class="input-group">
            <input type="text"
                v-model="message"
                id="message-box"
                class="form-control"
                :placeholder="(Object.keys(connections).length == 0 ? 'Nobody is here' : 'Type a message')"
                :disabled="(Object.keys(connections).length == 0)"
                v-on:keyup.enter="sendMessage" />
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
    .btn {
        border-radius: 0px;
    }
    .peer-video-rebinding-wait {
        z-index:2147483639;
        background:#000;
        color:#fff;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
    }
    .peer-video-rebinding-wait >>> h1 {
        margin-top:50vh;
    }
    #message-box {
        border-radius:0px;
    }
    .btn-show-messages {
        z-index:3;
    }
    .no-video-connections {
        padding: 4vh;
        text-align: center;
    }
    .chat-disabled >>> input {
        opacity:.5;
    }
    .chat-disabled >>> button {
        opacity:.5;
    }
    .btn-leave-chat {
        position: absolute;
        width: 25%;
        top: 0px;
        left: 50%;
        margin-top: 6px;
        margin-left: -12.5%;
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

    .btn-off {
        opacity: 0.75;
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
    video.peer-video-fullscreen {
        box-shadow: none;
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
    /* Initial position */
    #local-video-container {
        margin-top:20px;
        position:fixed;
        right:5em;
        border-radius:3px;
        z-index: 2147483638;
    }

    /* When fullscreened, shift things around*/
    .chat-container.peer-video-fullscreen {
        height:0px !important;
    }
    #local-video-container.local-video-overlay,
    #local-video-container.local-video-overlay >>> video {
        margin-right:0px;
        bottom:0px;
        right:0px;
    }

    .message-box.peer-video-fullscreen {
        z-index:3;
    }

    /*Videos container shrink so messages and stuff shows correctly*/
    #peer-videos-container.peer-video-fullscreen >>> .video-connections {
            height:0px;
    }

    /* Main Video Fullscreen */
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
import Modal from '../models/Modal.js';

export default {
    props: {
        chatName: String
    },
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
            ui: {
                deviceAccess: true,
                screenshareAccess: false,
                anonUsername: '',
                fullscreen: {active: false, target:null, wait:false},
                showMessagesFullscreen: false,
                dblClickTimer: null,
                sound: null
            }
        }
    },
    methods: {
        changeSettings(e) {
            var vm = this;
            var options = {
                props: {
                    close: {text: "Save and close"},
                    userPreferredBandwidth: vm.user.preferredBandwidth,
                    userDevices: vm.user.devices //Use a cloned value so we don't pre-emptively update stuff
                }
            };

            var modal = new Modal(vm.$refs.modalcontainer, options, 'settings');

            //Store the old settings to check against because vue binding already applied them
            var oldSettings = {video: vm.user.devices.active.video, audio: vm.user.devices.active.audio};

            modal.$on('close', function(preferred) {
                //We changed our audio / video device. Restart the stream stuff
                if(oldSettings.video != preferred.video
                || oldSettings.audio != preferred.audio) {

                    vm.user.devices.active.video = preferred.video;
                    vm.user.devices.active.audio = preferred.audio;
                    vm.user.preferredBandwidth = preferred.bandwidth;

                    //If we're currently streaming, turn it off
                    if(vm.stream.videoenabled) {
                        vm.stopLocalStream();
                        vm.stream.videoenabled = false;
                        vm.stream.screenshareenabled = false;
                    }
                }

                // Didn't change the bandwidth so just exit
                if(vm.user.preferredBandwidth == preferred.bandwidth) {
                    return;
                }

                vm.user.preferredBandwidth = preferred.bandwidth;

                //Update the preferred bandwidth on all it's peers
                for(var id in vm.connections) {
                    //If we're streaming to them then kill it
                    vm.connections[id].setPreferredBandwidth(preferred.bandwidth);

                    //renegotiate the connection for the new quality
                    vm.connections[id].connection.negotiate();
                }

            });
        },
        closeFullscreenOnDestroy(e) {
            //If the video stream is getting destroyed, wait 1s to see if it comes back
            //Possibly with a different camera or something
            var vm = this;
            vm.ui.fullscreen.wait = true;

            /*console.log("-closeFullscreenOnDestroy ---------");
            console.log(e);
            console.log(JSON.stringify(vm.ui.fullscreen));
            console.log("------------------------------------");*/

            //If the fullscreen that closed was the one we were looking at
            //if(vm.ui.fullscreen.target == e.peerid) {
                //Mark that we want to rebind this peer
                //vm.ui.fullscreen.rebind = true;

            //Wait 1s to actually close the fullscreen
            setTimeout(function() {
                var rebound = false;

                //Confirm that we rebound to something
                for(var i=0; i < vm.peerStreams.length; i++) {
                    //Duplicate stream! Ignore it
                    if(vm.peerStreams[i].peerid == e.peerid) {
                        //console.log("Was rebound!");
                        //console.log(vm.peerStreams[i]);
                        rebound = true;
                        break;
                    }
                }

                //Still waiting for a rebind but expired. Close the fullscreen
                if(!rebound) {
                    //console.log("Not rebound!");
                    vm.ui.fullscreen.target = null;
                    vm.ui.fullscreen.active = false;
                    vm.ui.fullscreen.wait = false;

                    //vm.$forceUpdate();
                } else {
                    vm.ui.fullscreen.wait = false;
                }
            }, 1000);
            //}
        },
        confirmLeave(e) {
            var vm = this;

            var options = {
                props: {
                    close: {text: "Go back"},
                    confirm: {
                        text: "Leave",
                        class: "btn btn-danger"
                    }
                },
                header: "<h1>Confirm Leave</h1>",
                body: "Are you sure you want to leave this chat?"
            };

            var modal = new Modal(vm.$refs.modalcontainer, options);

            modal.$on('confirm', function(e) {
                //Let everyone know I'm leaving so they don't sit then hanging
                for(var id in vm.connections) {
                    //If we're streaming to them then kill it
                    vm.connections[id].removeStream(vm.stream.connection);
                    vm.connections[id].destroy();
                }
                //Go back to the homepage
                window.location.href = '/';
            });
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
            var options = {video: {cursor: "always"}, audio: vm.user.devices.audio.length > 0};

            if(vm.stream.videoenabled && !vm.stream.screenshareenabled) {
                //console.log(options);
                //Even with audio:true getDisplayMedia doesn't return audio tracks
                navigator.mediaDevices.getDisplayMedia(options).then(function(stream) {
                    //Make sure they have a mic
                    if(options.audio) {
                        //Get and add the audio tracks manually
                        navigator.mediaDevices.getUserMedia({audio: true}).then(function(audioStream) {
                            audioStream.getAudioTracks().forEach(function(track){
                                stream.addTrack(track);
                            });

                            vm.stopLocalStream();
                            vm.stream.videoenabled = false;
                            vm.stream.screenshareenabled = true;
                            vm.stream.connection = stream;
                            vm.onLocalStream(stream);
                        });
                    } else {
                        //Just start the screen capture with no audio
                        vm.stopLocalStream();
                        vm.stream.videoenabled = false;
                        vm.stream.screenshareenabled = true;
                        vm.stream.connection = stream;
                        vm.onLocalStream(stream);
                    }

                }).catch((e) => {
                    vm.stream.screenshareenabled = false;
                    console.log("Local Screenshare Stream Error!");
                    console.log(e);
                    var modal = new Modal(vm.$refs.modalcontainer, {
                        header: "<h1>Not supported</h1>",
                        body: "<p>Could not start a screenshare. It seems your device does not support this functionality.</p>"
                    });
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
            var position = e.target.getBoundingClientRect();

            if(vm.stream.localsize == 'lg') {
                vm.stream.localsize = 'md'
            } else if(vm.stream.localsize == 'md') {
                vm.stream.localsize = 'sm'
                e.target.style.top = (position.y + 10) + "px"
                e.target.style.left = (position.x + 50) + "px"
            } else if(vm.stream.localsize == 'sm') {
                vm.stream.localsize = 'md'
                e.target.style.top = (position.y - 50) + "px"
                e.target.style.left = (position.x - 50) + "px"
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
                vm.stream.audioenabled = false;
                vm.setLocalAudio(vm.stream.connection, false);
            } else if((vm.stream.videoenabled || vm.stream.screenshareenabled) && !vm.stream.audioenabled) {
                vm.stream.audioenabled = true;
                vm.setLocalAudio(vm.stream.connection, true);
            }
        },
        setLocalAudio(stream, enabled) {
            var vm = this;
            stream.getAudioTracks().forEach(function(track){track.enabled = enabled;});
        },
        enableLocalAudio(stream) {
            var vm = this;
        },
        toggleVideo(e) {
            var vm = this;

            if(typeof navigator.mediaDevices == 'undefined') {
                alert("Something went wrong and your device does not support video");
                return;
            }


            //Turn off screensharing and swap back to video
            if(!vm.stream.videoenabled && vm.stream.screenshareenabled) {
                vm.stopLocalStream();
                vm.stream.screenshareenabled = false;
            }

            //console.log("Available Devices:");
            //console.log(vm.user.devices);

            if(!vm.stream.videoenabled) {
                console.log("Turning on camera...");
                //Default to video: true, audio: true to just use the defaults
                var options = {
                        video: vm.user.devices.video.length > 0,
                        audio: vm.user.devices.audio.length > 0
                    };
                if(vm.user.devices.active.video != null) {
                    console.log("Turning video on with camera id " + vm.user.devices.active.video);
                    options.video = {deviceId: { ideal: vm.user.devices.active.video }};
                }
                if(vm.user.devices.active.audio != null) {
                    console.log("Turning video on with camera id " + vm.user.devices.active.video);
                    options.audio = {deviceId: { ideal: vm.user.devices.active.audio }};
                }
                try {
                    navigator.mediaDevices.getUserMedia(options).then(function(stream) {
                        vm.stream.videoenabled = true;
                        vm.stream.screenshareenabled = false;

                        vm.onLocalStream(stream);
                    }).catch((e) => {
                        //They have devices but are probably blocked
                        var modal = new Modal(vm.$refs.modalcontainer, {
                            header: "<h1>Uh oh!</h1>",
                            body: "<p>Could not start your video feed. Did you block the browser permission?</p>" +
                                    "<p>Click the <i class='fas fa-lock'></i><span class='sr-only'>lock</span> icon in the URL to check your permissions and reload this page.</p>"
                        });

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
            //Add the elements in reverse so that the log trickles from the bottom up
            vm.chatLog.unshift({index: vm.chatLog.length, message: data, user: user, self: self});
        },
        outputConnections () {
            var vm = this;

            //Update for anything that's binding to Object.keys
            vm.$forceUpdate();

            var networkChartData = {nodes:[{id: 'me', name: 'Me'}], links:[]};


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
            //console.log("Sending");
            //console.log(networkChartData);
            vm.$refs.networkGraph.update(networkChartData);

        },
        /**
         * When a peer opens a stream, show the new connection
         */
        onPeerStream(stream, peerid) {
            var vm = this;
            //console.log("On peer stream called @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@");

            //Check for duplicates incase buttons are spammed
            for(var i=0; i < vm.peerStreams.length; i++) {
                //Duplicate stream! Ignore it
                if(vm.peerStreams[i].id == stream.id) {
                    //This stream already existed on this id. Remove it before we re-add it
                    console.log("Remove duplicate stream");
                    vm.connections[peerid].removeStream(vm.peerStreams[i]);
                    vm.peerStreams.splice(i, 1);
                }
            }

            stream.peerid = peerid;
            stream.peerConnection = vm.connections[peerid];

            /*console.log("On peer Stream ----------------------------");
            console.log("Peer id: " + JSON.stringify(peerid));
            console.log(stream.peerConnection);
            console.log(JSON.stringify(vm.ui.fullscreen));*/


            //We want to rebind a fullscreen stream on a specific target/hostid
            //if(vm.ui.fullscreen.rebind && vm.ui.fullscreen.target == peerid) {
            if(vm.ui.fullscreen.target == peerid) {
                //console.log("Rebind!");
                //We found our stream so we don't want to rebind anymore
                stream.startFullscreen = true;
                vm.$forceUpdate();
            } else {
                //console.log("Don't bind!");
                stream.startFullscreen = false;
            }
            //console.log("--------------------------------------------");
            vm.connections[peerid].setStream(stream);
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
                        /*console.log("Remove track----------");
                        //console.log("Set rebind flag")
                        console.log(JSON.stringify(vm.ui.fullscreen));
                        console.log(e.target.peerid);
                        console.log("------------------------");*/
                        //This was the target stream, set the rebind flag
                        if(e.target.peerid == vm.ui.fullscreen.target) {
                            vm.ui.fullscreen.wait = true;
                        }


                        vm.peerStreams.splice(i, 1);
                        break;
                    }
                }
            };
        },
        /**
         * Fires when a new local stream object has opened
         * Aka the user clicked the video button
         */
        onLocalStream(stream) {
            var vm = this;

            console.log("Local stream created - Set stream vars and tell everyone to retry");
            vm.stream.connection = stream;

            //Set this new streams audio settings
            vm.setLocalAudio(stream, vm.stream.audioenabled);

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
        //Sends the existing stream to any new peers
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
            //console.log("+Sending stream to " + id)
            //console.log(vm.stream.connection.getTracks());
            //console.log(client.streams);
            //client.removeStream(vm.stream.connection);

            if((vm.stream.videoenabled  || vm.stream.screenshareenabled) && !vm.connections[id].isStreaming) {
                //console.log("+APPLYING STREAM");
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
                //console.log("init (" + numHosts + ") hosts");

                for(var i=0;i<numHosts;i++) {

                    var peer = new PeerConnection(vm.server.signal, true, vm.user.preferredBandwidth);
                    var id = peer.id;
                    vm.connections[id] = peer;

                    vm.connections[id].connection.on('connect', function() {
                        console.log("Connection established between host -> client");
                        vm.ui.sound.play('connect');

                        vm.outputConnections();

                        if(vm.stream.videoenabled || vm.stream.screenshareenabled) {
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
                        //console.log("Recieved peer stream");
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
                    //console.log("host - binding returned client info");
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
                //console.log("Got request to init a client for host " + obj.hostid);
                var id=obj.hostid;

                //Key to the host id since it can possibly reqest to init a bunch of times during the handshake
                if(typeof vm.connections[id] == 'undefined') {
                    //console.log("Init a peer for host " + id);
                    var peer = new PeerConnection(vm.server.signal, false, vm.user.preferredBandwidth);

                    vm.connections[id] = peer;
                    vm.connections[id].setHostId(obj.hostid);
                    vm.connections[id].setUser(obj.user);

                    vm.connections[id].connection.on('connect', function() {
                        console.log("Connection established between client -> host");
                        //console.log(this);
                        //console.log(vm.connections[id].user);
                        vm.ui.sound.play('connect');

                        vm.outputConnections();
                        if(vm.stream.videoenabled || vm.stream.screenshareenabled) {
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


                //console.log("Signal host (" + obj.hostid + ") connection to client");
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
    vm.ui.deviceAccess = typeof navigator.mediaDevices != 'undefined';
    //If not a mobile device, enable screenshare
    vm.ui.screenshareAccess = !(/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent));

    vm.user = new User();

    //Discover and set the devices before we init stuff
    vm.user.discoverDevices(function(devices) {
        vm.user.auth().then(function(response) {
            //Prompt for a name
            if(response.success) {
                vm.init();
            }
        });
    });

}
}








</script>