import Vue from 'vue';

const routes = require('@/routes.json');
import Router from '@shared/Router/router.min';

Router.setRoutingData(routes);

Vue.mixin({
    methods: {
        route: (name, params, absolute) => Router.generate(name, params, absolute),
    }
});
