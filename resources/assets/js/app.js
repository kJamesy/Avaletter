
/**
 * First we will load all of this project's JavaScript dependencies which
 * include Vue and Vue Resource. This gives a great starting point for
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the body of the page. From here, you may begin adding components to
 * the application, or feel free to tweak this setup for your needs.
 */
import VueRouter from 'vue-router';
Vue.use(VueRouter);

import VueProgressBar from 'vue-progressbar';
Vue.use(VueProgressBar, { color: 'rgb(143, 255, 199)', failedColor: 'red', height: '10px'});
Vue.component('pagination', require('vue-bootstrap-pagination'));

/**
 * Mailing Lists
 */
import MailingLists from './components/MailingLists.vue';

if ( $('#app-mailing-lists').length ) {
    new Vue({
        el: '#app-mailing-lists',
        components: {
            MailingLists
        }
    });
}


/**
 * Subscribers
 */
import Subscribers from  './components/Subscribers.vue';
import SubscribersList from './components/SubscribersList.vue';
import SubscribersNew from  './components/SubscribersNew.vue';
import SubscribersImport from  './components/SubscribersImport.vue';
import SubscribersEdit from './components/SubscribersEdit.vue';
import SubscribersTrash from './components/SubscribersTrash.vue';


if ( $('#app-subscribers').length ) {
    var router = new VueRouter({
        mode: 'history',
        base: subscribersLinks.baseUri,
        linkActiveClass: 'active',
        routes: [
            { path: '/all', name: 'subscribers.index', component: SubscribersList },
            { path: '/:id(\\d+)/edit', name: 'subscribers.edit', component: SubscribersEdit },
            { path: '/:mList(\\d+)/mailing-list', name: 'subscribers.mailing_list', component: SubscribersList },
            { path: '/new', name: 'subscribers.new', component: SubscribersNew },
            { path: '/batch-import', name: 'subscribers.import', component: SubscribersImport },
            { path: '/trash', name: 'subscribers.trash', component: SubscribersTrash },
            { path: '*', redirect: { name: 'subscribers.index' } }
        ]
    });

    new Vue({
        el: '#app-subscribers',
        components: {
            Subscribers
        },
        router: router
    });
}


/**
 * Templates
 */
import EmailTemplates from  './components/EmailTemplates.vue';
import EmailTemplatesList from './components/EmailTemplatesList.vue';
import EmailTemplatesNew from  './components/EmailTemplatesNew.vue';
import EmailTemplatesEdit from './components/EmailTemplatesEdit.vue';


if ( $('#app-email-templates').length ) {
    var templatesRouter = new VueRouter({
        mode: 'history',
        base: emailTemplatesLinks.baseUri,
        linkActiveClass: 'active',
        routes: [
            { path: '/all', name: 'emailTemplates.index', component: EmailTemplatesList },
            { path: '/:id(\\d+)/edit', name: 'emailTemplates.edit', component: EmailTemplatesEdit },
            { path: '/new', name: 'emailTemplates.new', component: EmailTemplatesNew },
            { path: '*', redirect: { name: 'emailTemplates.index' } }
        ]
    });

    new Vue({
        el: '#app-email-templates',
        components: {
            EmailTemplates
        },
        router: templatesRouter
    });
}