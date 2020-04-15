import hark from 'hark';

export default class PeerConnection {

    constructor(server, initiator) {
        var Peer = require('simple-peer');
        var self = this;
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

    //This peers stream
    setStream(stream) {
        var self = this;
        self.stream = stream;

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

    //Sends a local stream to this peer
    addStream(stream) {
        if(this.isStreaming) {
            console.log("ALREADY STREAMING");
        } else {
            //console.log(this.connection);
            this.connection.addStream(stream);
            this.isStreaming = true;
        }

    }

    //Removes a local stream from this peer
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