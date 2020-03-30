import Vue from 'vue';
import transFilter from 'vue-trans';

// install mixin for use named backend routes in vue
require('@shared/Router/vue-router');
window.dayjs = require('dayjs');

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

Vue.use(transFilter);
