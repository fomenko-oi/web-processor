// any CSS you import will output into a single css file (app.css in this case)
import '../css/app.css';
import './bootstrap';
import Vue from 'vue';
import SongComponent from "@components/Services/Yandex/SongComponent";

Vue.component('yandex-download-song-component', SongComponent);

new Vue({
    el: '#app',
});
