window.Vue = require('vue').default;
window.axios = require('axios').default;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import {BootstrapVue, IconsPlugin, ModalPlugin} from 'bootstrap-vue';
import './app.scss';
import Vue from 'vue';

Vue.use(BootstrapVue);
Vue.use(IconsPlugin);
Vue.use(ModalPlugin);

const files = require.context('./', true, /\.vue$/i)
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

let elements = document.getElementsByClassName('vue')
for (let el of elements) {
    new Vue({
        el: el
    });
}