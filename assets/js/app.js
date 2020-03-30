// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.css';
import './bootstrap';
import Vue from 'vue';
import VueRouter from 'vue-router'
import SongComponent from "@components/Services/Yandex/SongComponent";

Vue.use(VueRouter);

const router = new VueRouter({
    routes: [
        { path: '/yandex/song/:song', component: SongComponent, name: 'yandex.song' },
    ],
    mode: 'history'
});

Vue.component('yandex-download-song-component', SongComponent);

new Vue({
    el: '#app',
    router
});
