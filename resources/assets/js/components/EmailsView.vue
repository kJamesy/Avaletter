<template>
    <div class="emails-view" v-cloak="">
        <vue-progress-bar></vue-progress-bar>

        <i v-if="fetching" class="fa fa-circle-o-notch fa-spin fa-4x fa-fw"></i>

        <div v-if="! fetching && email ">
            <div class="row">
                <div class="col-sm-12" style="margin: 30px 0">
                    <router-link v-bind:to="{ name: 'emails.index' }" title="Sent Emails" class="btn btn-default btn-xs" exact><i class="fa fa-list"></i></router-link>
                    &nbsp; &nbsp;
                    <router-link v-bind:to="{ name: 'emails.stats', params: { id: email.id } }" title="Stats" class="btn btn-default btn-xs" exact><i class="fa fa-bar-chart"></i></router-link>
                    &nbsp; &nbsp;
                    <router-link v-bind:to="{ name: 'emails.forward', params: { id: email.id } }" title="Forward" class="btn btn-default btn-xs" exact><i class="fa fa-long-arrow-right"></i></router-link>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <h3>{{ email.subject }}</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-3 col-sm-2">
                    Created:
                </div>
                <div class="col-xs-8 col-sm-10">
                    {{ email.created_at | localTime }}
                </div>
            </div>
            <div class="row">
                <div class="col-xs-3 col-sm-2">
                    Sent:
                </div>
                <div class="col-xs-8 col-sm-10">
                    {{ email.sent_at | localTime }}
                </div>
            </div>
            <div class="row view-row">
                <div class="col-xs-3 col-sm-2">
                    From:
                </div>
                <div class="col-xs-8 col-sm-10">
                    {{ email.from }}
                </div>
            </div>
            <div class="row view-row">
                <div class="col-xs-3 col-sm-2">
                    User:
                </div>
                <div class="col-xs-8 col-sm-10">
                    {{ email.user.first_name }} {{ email.user.last_name }}
                </div>
            </div>
            <div class="row view-row">
                <div class="col-xs-3 col-sm-2">
                    Email Edition:
                </div>
                <div class="col-xs-8 col-sm-10">
                    {{ email.email_edition.edition }}
                </div>
            </div>
            <div class="row view-row">
                <div class="col-xs-3 col-sm-2">
                    Recipients:
                </div>
                <div class="col-xs-8 col-sm-10">
                    <label for="viewRecipients"></label>
                    <select v-model="viewRecipients" id="viewRecipients" >
                        <option v-for="option in viewOptions" v-bind:value="option.value">
                            {{ option.text }}
                        </option>
                    </select>
                </div>
            </div>
            <hr />
            <div class="row">
                <div class="col-sm-12">
                    <iframe v-if="! viewingRecipients && ! fetchingRecipients" v-bind:src="email.url" style="width: 100%; border:none;" scrolling="no" v-on:load="resizeIframe($event)"></iframe>

                    <i v-if="fetchingRecipients" class="fa fa-circle-o-notch fa-spin fa-4x fa-fw"></i>
                    <div v-if="viewingRecipients && ! fetchingRecipients">
                        <pagination :pagination="pagination" :callback="getRecipients" :options="paginationOptions"></pagination>
                        <div style="float: right; margin: 20px 0;">
                            Page {{ pagination.current_page }} of {{ pagination.last_page }} ({{ pagination.total }} records)
                        </div>
                        <div class="clearfix"></div>
                        <table class="table table-responsive table-striped">
                            <thead>
                            <tr>
                                <th>Recipient </th>
                                <th v-if="viewRecipients == 'injections'">Status</th>
                                <th v-if="viewRecipients == 'deliveries'">Delivered</th>
                                <th v-if="viewRecipients == 'opens'">Read</th>
                                <th v-if="viewRecipients == 'clicks'">Link Clicked On (frequency)</th>
                                <th v-if="viewRecipients == 'undelivered'">Active</th>
                                <th v-if="viewRecipients == 'undelivered'">Deleted</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="subscriber in orderedSubscribers">
                                <td>{{ getSubscriberLabel(subscriber) }}</td>
                                <td v-if="viewRecipients == 'injections'"><i class="fa" v-bind:class="getStatusIcon(subscriber)"></i></td>
                                <td v-if="viewRecipients == 'deliveries'">{{ subscriber.email_deliveries[0].delivered_at | localTime }}</td>
                                <td v-if="viewRecipients == 'opens'">{{ subscriber.email_opens[0].opened_at | localTime }}</td>
                                <td v-if="viewRecipients == 'clicks'" v-html="getClicks(subscriber)"></td>
                                <td v-if="viewRecipients == 'undelivered'"><i class="fa" v-bind:class='activeIcon(subscriber.active)'></i></td>
                                <td v-if="viewRecipients == 'undelivered'"><i class="fa" v-bind:class='activeIcon(subscriber.is_deleted)'></td>
                            </tr>
                            </tbody>
                        </table>
                        <pagination :pagination="pagination" :callback="getRecipients" :options="paginationOptions"></pagination>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
    export default {
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = emailsLinks.baseUri;
                this.pagination = this.getInitialPagination();
                this.getSentEmail();
            });
        },
        data() {
            return {
                id: this.$route.params.id,
                email: null,
                fetching: true,
                fetchingRecipients: false,
                viewingRecipients: false,
                progressPauseAt: null,
                progressPercentage: 0,
                viewRecipients: '',
                viewOptions: [
                    { text: 'Select Recipients to View', value: '' },
                    { text: 'All Recipients', value: 'injections' },
                    { text: 'Delivered', value: 'deliveries' },
                    { text: 'Read', value: 'opens' },
                    { text: 'Clicked', value: 'clicks' },
                    { text: 'Not Delivered', value: 'undelivered' },
                ],
                pagination: {},
                paginationOptions: {
                    offset: 5,
                    alwaysShowPrevNext: true
                },
                currentRecipients: []
            }
        },
        computed: {
            orderedSubscribers() {
                return _.orderBy(this.currentRecipients, ['first_name', 'last_name'], ['asc']);
            }
        },
        methods: {
            getSentEmail() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();
                vm.progressPauseAt = moment().add(5, 'seconds');
                vm.progressTimer();
                vm.fireSpecialWatchers();

                vm.$http.post(vm.resourceUrl + '/' + vm.id + '/sent-email').then(function(response) {
                    if (response.data) {
                        if ( response.data.email ) {
                            vm.email = response.data.email;
                            vm.viewOptions = [
                                { text: 'Select Recipients to View', value: '' },
                                { text: 'All Recipients (' + vm.email.email_injections_count + ')', value: 'injections' },
                                { text: 'Delivered (' + vm.email.email_deliveries_count + ')', value: 'deliveries' },
                                { text: 'Read (' + vm.email.email_opens_count + ')', value: 'opens' },
                                { text: 'Clicked (' + vm.email.email_clicks_count + ')', value: 'clicks' },
                                { text: 'Not Delivered (' + ( vm.email.email_injections_count - vm.email.email_deliveries_count ) + ')', value: 'undelivered' }
                            ];
                            vm.viewingRecipients = false;
                            vm.fetching = false;
                            progress.finish();
                        }
                        else {
                            progress.fail();
                            swal({ title: "An Error Occurred", text: 'The email does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                                vm.$router.replace({ name: 'emails.index' });
                            });
                        }
                    }
                }, function(error) {
                    progress.fail();
                    swal({ title: "An Error Occurred", text: 'The email does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                        vm.$router.replace({ name: 'emails.index' });
                    });
                });
            },
            getRecipients() {
                var vm = this;
                var progress = vm.$Progress;
                var lastPage = _.ceil(vm.pagination.total / vm.pagination.per_page);
                var page = ( lastPage < vm.pagination.last_page ) ? 1 : vm.pagination.current_page;

                progress.start();
                vm.currentRecipients = [];
                vm.progressPauseAt = moment().add(5, 'seconds');
                vm.progressTimer();
                vm.fireSpecialWatchers();
                vm.fetchingRecipients = true;

                vm.$http.post(vm.resourceUrl + '/' + vm.id + '/sent-email/recipients', { statType: vm.viewRecipients, page: page }).then(function(response) {
                    if (response.data) {
                        if ( response.data.subscribers ) {
                            var subscribers = response.data.subscribers;
                            vm.currentRecipients = subscribers.data;

                            vm.$set(vm, 'pagination', {
                                total: subscribers.total,
                                per_page: subscribers.per_page,
                                current_page: subscribers.current_page,
                                last_page: subscribers.last_page,
                                from: subscribers.from,
                                to: subscribers.to
                            });

                            vm.viewingRecipients = true;
                            vm.fetchingRecipients = false;
                            progress.finish();
                        }
                        else {
                            progress.fail();
                            swal({ title: "An Error Occurred", text: 'Recipients do not exist', type: 'error', animation: 'slide-from-top'}, function() {
                                vm.viewingRecipients = false;
                                vm.fetchingRecipients = false;
                            });
                        }
                    }
                }, function(error) {
                    progress.fail();
                    swal({ title: "An Error Occurred", text: 'Recipients do not exist', type: 'error', animation: 'slide-from-top'}, function() {
                        vm.viewingRecipients = false;
                        vm.fetchingRecipients = false;
                    });
                });
            },
            progressTimer() {
                var vm = this;

                if ( vm.progressPauseAt ) {
                    _.delay(function () {
                        if ( vm.progressPercentage > 80 && ( moment().isSame(vm.progressPauseAt) || moment().isAfter(vm.progressPauseAt) ) ) {
                            vm.$Progress.pause();
                            vm.progressPauseAt = null;
                        }

                        vm.progressTimer();
                    }, 500);
                }
            },
            fireSpecialWatchers() {
                this.$watch(function() {
                            return this.$Progress.get();
                        }, function(newVal) {
                            if ( newVal > 0 && newVal < 100 )
                                this.progressPercentage = newVal;
                            else
                                this.progressPercentage = 0;
                        }
                )
            },
            getSubscriberLabel(subscriber) {
                return subscriber.first_name + ' ' + subscriber.last_name + ' <' + subscriber.email + '>';
            },
            getStatusIcon(subscriber) {
                if ( subscriber.email_opens && subscriber.email_opens.length )
                    return 'fa-envelope-open-o';
                else if ( subscriber.email_deliveries && subscriber.email_deliveries.length )
                    return 'fa-envelope';
                else
                    return 'fa-question-circle';
            },
            getClicks(subscriber) {
                var links = '';
                var clicks = subscriber.email_clicks;
                var num = clicks.length;

                if ( num ) {
                    _.forEach(clicks, function(click, index) {
                        links += click.target_link + ' (' + click.hits + ')';
                        if ( index != num - 1 )
                            links += ' <br /> ';
                    });
                }
                return links;
            },
            getInitialPagination() {
                return {
                    total: 0,
                    per_page: 100,
                    current_page: 1,
                    last_page: 0,
                    from: 1,
                    to: 100
                };
            },
            resizeIframe(event) {
                var iframe = event.target;
                if ( iframe )
                    iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
            },
            activeIcon(activeAttr) {
                return activeAttr ? 'fa-check' : 'fa-times';
            }
        },
        watch: {
            'viewRecipients'(newVal) {
                if ( newVal.length ) {
                    this.pagination = this.getInitialPagination();
                    this.getRecipients();
                }
                else
                    this.viewingRecipients = false;

            }
        },
        filters: {
            localTime(date) {
                return moment(date + ' Z', 'YYYY-MM-DD HH:mm:ss Z', true).format('D MMM YYYY HH:mm');
            }
        }
    }
</script>
