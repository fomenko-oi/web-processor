// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.css';
import './bootstrap';
import Vue from 'vue';
import VueRouter from 'vue-router';
import i18n from './locale';
import SongComponent from "@components/Services/Yandex/SongComponent";
import AlbumComponent from "@components/Services/Yandex/AlbumComponent";

Vue.use(VueRouter);

const router = new VueRouter({
    routes: [
        {path: '/yandex/song/:song', component: SongComponent, name: 'yandex.song'},
        {path: '/:lang/yandex/song/:song', component: SongComponent, name: 'yandex.song.local'},
        {path: '/yandex/album/:album', component: AlbumComponent, name: 'yandex.album'},
        {path: '/:lang/yandex/album/:album', component: AlbumComponent, name: 'yandex.album.local'},
    ],
    mode: 'history'
});

Vue.component('yandex-download-song-component', SongComponent);
Vue.component('yandex-download-album-component', AlbumComponent);

new Vue({
    el: '#app',
    router,
    i18n
});
