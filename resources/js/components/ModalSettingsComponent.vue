<template>
    <div>
        <modal-component v-on:confirm="$emit('confirm', userPreferred)" v-on:close="$emit('close', userPreferred)" v-bind:confirm="confirm" v-bind:close="close">

            <template v-slot:header>
                <h1>Settings</h1>
            </template>
            <template v-slot:body>
                <p>
                    <strong>Video Quality</strong>
                </p>
                <select class="form-control" v-model="userPreferred">
                    <option value="ultrahigh">Very High</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                    <option value="ultralow">Very Low</option>
                    <option value="trash">My Internet is Trash</option>
                </select>
            </template>
        </modal-component>

    </div>

</template>

<style scoped>

</style>

<script>

export default {
    name: "ModalSettingsComponent",
    props: {
        close: Object,
        confirm: Object,
        userPreferred: String
    },
    data: function () {
        return {

        }
    },
    methods: {

    },
    mounted() {
        var vm = this;

    }
}

</script>