require('./bootstrap');

import VueRouter from 'vue-router';
Vue.use(VueRouter);

import VueProgressBar from 'vue-progressbar';
Vue.use(VueProgressBar, { color: 'rgb(143, 255, 199)', failedColor: 'red', height: '10px'});
Vue.component('pagination', require('vue-bootstrap-pagination'));
import VueChartist from 'vue-chartist';
Vue.use(VueChartist);
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
 * Email Templates
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

/**
 * Email Editions
 */
import EmailEditions from './components/EmailEditions.vue';

if ( $('#app-email-editions').length ) {
    new Vue({
        el: '#app-email-editions',
        components: {
            EmailEditions
        }
    });
}

/**
 * Emails
 */
import Emails from  './components/Emails.vue';
import EmailsSent from './components/EmailsSent.vue';
import EmailsDrafts from './components/EmailsDrafts.vue';
import EmailsNew from  './components/EmailsNew.vue';
import EmailsEdit from './components/EmailsEdit.vue';
import EmailsForward from './components/EmailsForward.vue';
import EmailsTrash from './components/EmailsTrash.vue';
import EmailsStats from './components/EmailsStats.vue';
import EmailsView from './components/EmailsView.vue';

if ( $('#app-emails').length ) {
    var emailsRouter = new VueRouter({
        mode: 'history',
        base: emailsLinks.baseUri,
        linkActiveClass: 'active',
        routes: [
            { path: '/sent', name: 'emails.index', component: EmailsSent },
            { path: '/drafts', name: 'emails.drafts', component: EmailsDrafts },
            { path: '/:id(\\d+)/edit', name: 'emails.edit', component: EmailsEdit },
            { path: '/:id(\\d+)/forward', name: 'emails.forward', component: EmailsForward },
            { path: '/new', name: 'emails.new', component: EmailsNew },
            { path: '/trash', name: 'emails.trash', component: EmailsTrash },
            { path: '/:id(\\d+)/sent-email/stats', name: 'emails.stats', component: EmailsStats },
            { path: '/:id(\\d+)/sent-email', name: 'emails.view', component: EmailsView },
            { path: '*', redirect: { name: 'emails.index' } }
        ]
    });

    new Vue({
        el: '#app-emails',
        components: {
            Emails
        },
        router: emailsRouter
    });
}