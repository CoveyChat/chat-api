<template>
    <div id="control-panel-container"
        v-bind:class="{
            'fullscreen': inFullscreen
        }">
        <!--Settings Button-->
        <button
            class="btn btn-secondary"
            type="button"
            id="btn-local-video-toggle"
            v-bind:class="{
                'btn-off': !videoEnabled,
                'local-video-overlay': inFullscreen
            }"
            @click="$emit('changeSettings')">

            <span class="sr-only">Settings</span>
            <i class="fas fa-cog"></i>
        </button>


        <!--Local Video Button-->
        <button
            class="btn btn-primary"
            type="button"
            id="btn-local-video-toggle"
            v-bind:class="{
                'btn-off': !videoEnabled,
                'local-video-overlay': inFullscreen
            }"
            v-if="deviceAccess"
            @click="$emit('toggleVideo')">

            <span class="sr-only" v-if="!videoEnabled">Start Video</span>
            <i class="fas fa-video-slash" v-if="!videoEnabled"></i>

            <span class="sr-only" v-if="videoEnabled">Stop Video</span>
            <i class="fas fa-video" v-if="videoEnabled"></i>
        </button>

        <!--Local Audio Button-->
        <button class="btn btn-danger"
            type="button"
            id="btn-local-audio-toggle"
            v-bind:class="{
                'btn-off': !audioEnabled,
                'local-audio-overlay': inFullscreen
            }"
            v-if="videoEnabled || screenshareEnabled"
            @click="$emit('toggleAudio')">

            <span class="sr-only" v-if="!audioEnabled">Enable Audio</span>
            <i class="fas fa-microphone-slash" v-if="!audioEnabled"></i>

            <span class="sr-only" v-if="audioEnabled">Mute Video</span>
            <i class="fas fa-microphone" v-if="audioEnabled"></i>
        </button>

        <!--Local Screenshare Button-->
        <button class="btn btn-success"
            type="button"
            id="btn-local-screenshare-toggle"
            v-bind:class="{
                'btn-off': !screenshareEnabled,
                'local-screenshare-overlay': inFullscreen
            }"
            v-if="videoEnabled || screenshareEnabled"
            @click="$emit('toggleScreenshare')">

            <span class="sr-only" v-if="!screenshareEnabled">Enable Screenshare</span>
            <i class="fas fa-desktop" v-if="!screenshareEnabled"></i>
            <i class="fas fa-slash" v-if="!screenshareEnabled"></i>

            <span class="sr-only" v-if="screenshareEnabled">Stop Sharing</span>
            <i class="fas fa-desktop" v-if="screenshareEnabled"></i>
        </button>

        <!--Switch Video Feed Button-->
        <button class="btn btn-primary"
            type="button"
            id="btn-local-swapvideo-toggle"
            v-bind:class="{
                'local-swapvideo-overlay': inFullscreen
            }"
            v-if="videoEnabled && videoDevices.length > 1"
            @click="$emit('swapVideoFeed')">

            <span class="sr-only">Switch Video</span>
            <i class="fas fa-sync-alt"></i>
        </button>

    </div>
</template>

<style scoped>
    #control-panel-container {
        position: fixed;
        z-index:2147483630 !important;
        right:0px;
        width:4em;
    }

    button.btn {
        right:10px;
        border-radius: 2em !important;
        width: 4em;
        height: 4em;
        margin-top:0.5em;
    }

    /**Adjust the slash since font awesome doesn't offer a video slash option */
    #btn-local-screenshare-toggle >>> .fa-slash {
        display:block;
        margin-top:-20px;
    }

    .btn-off {
        opacity: 0.75;
    }

    /*
    When in Fullscreen
     */
    #control-panel-container.fullscreen {
        margin-top:0px !important;
        top:0px;
        right:0px !important;
    }

</style>

<script>

export default {
    props: {
        inFullscreen: Boolean,
        deviceAccess: Boolean,
        videoEnabled: Boolean,
        audioEnabled: Boolean,
        screenshareEnabled: Boolean,
        videoDevices: Array
    },
    data: function () {
        return {
            showCopiedBadge: false
        }
    },
    methods: {
        onClick(e) {
            var vm = this;
            navigator.clipboard.writeText(vm.text);
            vm.showCopiedBadge = true;

            setTimeout(function() {
                vm.showCopiedBadge = false
            }, 300);
        }
    },
mounted() {
    console.log('Copy Component mounted.');
    //View model reference for inside scoped functions
    var vm = this;

}
}

</script>