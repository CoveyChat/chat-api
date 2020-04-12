/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

const componentFiles = require.context('./components/', true, /\.vue$/i)
componentFiles.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], componentFiles(key).default))

const directiveFiles = require.context('./directives/', true, /\.js/i)
directiveFiles.keys().map(key => Vue.directive(key.split('/').pop().split('.')[0], directiveFiles(key).default))

const app = new Vue({
    el: '#app',
});
