<template>
    <div id="messages" ref="messages" class="overflow-auto d-flex flex-grow-1 flex-column">
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
</template>

<style scoped>
    #messages {
        /*This acts as a "minimum" height for when flexbox wants to collapse down to nothing
        Very important for mobile since videos "stack" and will otherwise collapse the messages
        area down to nothing */
        height: 300px;
    }
</style>

<script>

export default {
    props: {
        chatLog: Array
    },
    data: function () {
        return {

        }
    },
    methods: {
        animateScroll(duration) {
            var vm = this;
            var messages = this.$refs['messages'];

            var start = messages.scrollTop;
            var end = messages.scrollHeight;
            var change = end - start;
            var increment = 20;
            function easeInOut(currentTime, start, change, duration) {
                // by Robert Penner
                currentTime /= duration / 2;
                if (currentTime < 1) {
                return change / 2 * currentTime * currentTime + start;
                }
                currentTime -= 1;
                return -change / 2 * (currentTime * (currentTime - 2) - 1) + start;
            }
            function animate(elapsedTime) {
                elapsedTime += increment;
                var position = easeInOut(elapsedTime, start, change, duration);
                messages.scrollTop = position;
                if (elapsedTime < duration) {
                setTimeout(function() {
                    animate(elapsedTime);
                }, increment)
                }
            }
            animate(0);
        }
    },
mounted() {
    console.log('Messages Component mounted.');
    //View model reference for inside scoped functions
    var vm = this;

},
updated() {
    var vm = this;
    vm.animateScroll(300);
}
}

</script>