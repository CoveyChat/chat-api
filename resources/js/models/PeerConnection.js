import hark from 'hark';
import adapter from 'webrtc-adapter';
import sdpTransform from 'sdp-transform';

export default class PeerConnection {

    constructor(server, initiator) {
        var Peer = require('simple-peer');
        var self = this;

        self.videoBitrate = 125;
        self.audioBitrate = 64;

        self.events = {
            speaking: new Event('speaking'),
            stopped_speaking: new Event('stopped_speaking')
        };
        self.connection = new Peer({
            initiator: initiator,
            config: {
                iceServers: [
                    {urls: 'stun:bevy.chat'},
                    {urls: 'turn:bevy.chat', username: 'bevychat', credential: 'bevychatturntest'}
                ]
            },
            sdpTransform: function (sdp) {
                var sdpObj = sdpTransform.parse(sdp);

                //Go through all the media and apply the bitrate changes
                for(var i=0;i<sdpObj.media.length;i++) {
                    var mediaItem = sdpObj.media[i];
                    var newBitrate = 0;
                    if(mediaItem.type == 'audio') {
                        newBitrate = self.audioBitrate;
                        continue;
                    } else if(mediaItem.type == 'video') {
                        newBitrate = self.videoBitrate;
                    } else {
                        //Not an audio/video media item. Skip it
                        continue;
                    }

                    //Go through all the rtp settings and apply the new rate
                    for(var j=0;j<sdpObj.media[i].rtp.length;j++) {
                        //Convert to kbps
                        sdpObj.media[i].rtp[j].rate = newBitrate * 1000;
                    }

                }

                var updatedSdp = sdpTransform.write(sdpObj);

                /*
                //var sdp2 = self.setMediaBitrate(sdp, 'video', self.videoBandwidth);
                //sdp2 = setMediaBitrate(sdp2, 'audio', self.audioBandwidth);
                console.log("SDP");
                //console.log(self.connection._wrtc.RTCPeerConnection);
                var rawCon = self.connection._pc;
                var senders = rawCon.getSenders();
                //console.log(self.connection._wrtc.getRTCPeerConnection());
                if(senders.length > 0) {
                    console.log(rawCon);
                    console.log(rawCon.getSenders());
                    console.log(senders[0].getParameters());
                    console.log(senders[0]);
                    const parameters = senders[0].getParameters();

                    if (!parameters.encodings || parameters.encodings.length == 0) {
                        parameters.encodings = [{maxBitrate: null}];
                    }
                    console.log(parameters.encodings[0]);
                    parameters.encodings[0].maxBitrate = 10 * 1000
                    senders[0].setParameters(parameters)
                    .then(() => {
                        console.log("Sent??");
                    })
                    .catch(e => console.error(e));
                }
                */

                return updatedSdp;
            }
        });
        self.server = server;

        self.id = self.connection._id;
        self.user = {name: "anonymous user", verified: false, isSpeaking: false};

        self.initiator = initiator;
        self.hostid = initiator ? self.id : null;
        self.clientid = initiator ? null : self.id;

        self.isStreaming = false;
        self.stream = null;

        self.boundElement = null;

        self.connection.on('connect', function() {
            console.log("~~~~~Connected!~~~~~");
        });

        self.connection.on('signal', function (webRtcId) {
            if(self.connection.initiator) {
                //console.log('Got initiator signal, sending off to client');
                self.server.emit('sendtoclient', {webRtcId: webRtcId, hostid: self.hostid, clientid: self.clientid});
            } else {
                //console.log('Got client signal, sending off to host');

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
            console.log(err);
            self.destroy();
        });

        return this;
    }

    //This peers stream
    setStream(stream) {
        var self = this;
        self.stream = stream;

        //Make sure there's audio tracks to bind to
        if(stream.getAudioTracks().length > 0) {
            var speechEvents = hark(stream);

            speechEvents.on('speaking', function() {
                self.user.isSpeaking = true;
                self.events.speaking.peer = self;
                document.dispatchEvent(self.events.speaking);
            });

            speechEvents.on('stopped_speaking', function() {
                self.user.isSpeaking = false;
                self.events.stopped_speaking.peer = self;
                document.dispatchEvent(self.events.stopped_speaking);
            });
        }
    }

    //Sends a local stream to this peer
    addStream(stream) {
        if(this.isStreaming) {
            //console.log("ALREADY STREAMING");
        } else {
            //console.log(this.connection);
            this.connection.addStream(stream);
            this.isStreaming = true;
        }

    }

    //Removes a local stream from this peer
    removeStream(stream) {
        if(!this.isStreaming) {
            //console.log("NOT STREAMING");
        } else {
            this.connection.removeStream(stream);
            this.isStreaming = false;
        }

    }

    setHostId(id) {
        this.hostid = id;
        this.connection.hostid = id;
    }
    setClientId(id) {
        this.clientid = id;
        this.connection.clientid = id;
    }
    setUser(user) {
        this.user.name = user.name;
        this.user.verified = user.verified;
    }

    signal(webRtcId) {
        this.connection.signal(webRtcId);
    }

    destroy() {
        this.connection.destroy();
        return null;
    }
}