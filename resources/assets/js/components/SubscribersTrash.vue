<template>
    <div class="subscribers-trash" v-if="successfulFetch" v-cloak>
        <div class="clearfix">
            <h3 style="display: block;">Bin</h3>
            <form v-if="searching" v-on:submit.prevent="doSearch">
                <input type="text" v-model.trim="search" placeholder="Search" />
                &nbsp; <a v-on:click.prevent="cancelSearch" href="" title="Cancel Search" v-if="searching"><i class="fa fa-ban"></i></a>
            </form>
            &nbsp; <a v-on:click.prevent="exportSubscribers" href="" title="Export All" v-if="! searching"><i class="fa fa-arrow-circle-down"></i></a>
            &nbsp; <a v-on:click.prevent="initiateSearch" href="" title="Search" v-if="! searching"><i class="fa fa-search"></i></a>
        </div>
        <div style="float: left; margin: 20px 0;" v-if="selected.length">
            <label for="quick-edit">Quick Edit</label>
            <select v-model="quickEdit" id="quick-edit">
                <option v-for="option in quickEditOptions" v-bind:value="option.value">
                    {{ option.text }}
                </option>
            </select>
        </div>
        <div style="float: right; margin: 20px 0;">
            Page {{ pagination.current_page }} of {{ pagination.last_page }} ({{ pagination.total }} records)
            <label for="records_per_page">Records Per Page</label>
            <select v-model="pagination.per_page" id="records_per_page" >
                <option v-for="option in perPageOptions" v-bind:value="option.value">
                    {{ option.text }}
                </option>
            </select>
        </div>
        <div class="clearfix"></div>
        <table class="table">
            <thead>
                <tr>
                    <th><input type="checkbox" v-model="selectAll"></th>
                    <th>First Name <button v-on:click="changeSort('first_name')"><i v-bind:class="'fa ' + getSortIcon('first_name')"></i></button></th>
                    <th>Last Name <button v-on:click="changeSort('last_name')"><i v-bind:class="'fa ' + getSortIcon('last_name')"></i></button></th>
                    <th>Email <button v-on:click="changeSort('email')"><i v-bind:class="'fa ' + getSortIcon('email')"></i></button></th>
                    <th>Active <button v-on:click="changeSort('active')"><i v-bind:class="'fa ' + getSortIcon('active')"></i></button></th>
                    <th>Mailing Lists</th>
                    <th>Created <button v-on:click="changeSort('created_at')"><i v-bind:class="'fa ' + getSortIcon('created_at')"></i></button></th>
                    <th>Updated <button v-on:click="changeSort('updated_at')"><i v-bind:class="'fa ' + getSortIcon('updated_at')"></i></button></th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="subscriber in orderedSubscribers">
                    <td><input type="checkbox" v-model="selected" v-bind:value="subscriber.id"></td>
                    <td>{{ subscriber.first_name }}</td>
                    <td>{{ subscriber.last_name }}</td>
                    <td>{{ subscriber.email }}</td>
                    <td><i class="fa" v-bind:class='activeIcon(subscriber.active)'></i></td>
                    <td>{{ getSubscriberMLists(subscriber.mailing_lists) }}</td>
                    <td>{{ subscriber.created_at | localTime }}</td>
                    <td>{{ subscriber.updated_at | localTime }}</td>
                    <td></td>
                    <td><i class="fa fa-times btn btn-danger btn-xs" v-on:click="destroySubscriber(subscriber)"></i></td>
                </tr>
            </tbody>
        </table>
        <pagination :pagination="pagination" :callback="fetchSubscribers" :options="paginationOptions"></pagination>
    </div>
</template>
<script>
    export default{
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = subscribersLinks.baseUri;
                this.pagination = this.getInitialPagination();
                this.fetchSubscribers();
            });
        },
        data() {
            return {
                subscribers: [],
                orderToggle: ( userSubscribersSettings.order && userSubscribersSettings.order == 'asc' ) ? 1 : -1,
                orderAttr: ( userSubscribersSettings.order_by && userSubscribersSettings.order_by.length ) ? userSubscribersSettings.order_by : 'updated_at',
                defaultOrderAttr: 'updated_at',
                pagination: {},
                paginationOptions: {
                    offset: 5,
                    alwaysShowPrevNext: true
                },
                perPageOptions: [
                    { text: '25', value: 25} ,
                    { text: '50', value: 50 },
                    { text: '100', value: 100 },
                    { text: '500', value: 500 }
                ],
                quickEditOptions: [
                    { text: 'Select Option', value: '' },
                    { text: 'Export', value: 'export' },
                    { text: 'Activate', value: 'activate' },
                    { text: 'Deactivate', value: 'deactivate' },
                    { text: 'Restore', value: 'restore' },
                    { text: 'Destroy', value: 'destroy' },
                ],
                quickEdit: '',
                subscriberIds: [],
                selected: [],
                successfulFetch: false,
                search: '',
                searching: false
            }
        },
        computed: {
            orderedSubscribers() {
                return _.orderBy(this.subscribers, [this.orderAttr, this.defaultOrderAttr], [( this.orderToggle == 1 ) ? 'asc' : 'desc', 'desc']);
            },
            selectAll: {
                get() {
                    return this.subscriberIds ? this.selected.length == this.subscriberIds.length : false;
                },
                set(value) {
                    this.selected = value ? this.subscriberIds : [];
                }
            }
        },
        methods: {
            fetchSubscribers(orderAttr, orderToggle) {
                var vm = this;
                var orderBy = orderAttr ? orderAttr : vm.orderAttr;
                var order = orderToggle ? orderToggle : vm.orderToggle;
                var progress = vm.$Progress;
                var lastPage = _.ceil(vm.pagination.total / vm.pagination.per_page);
                vm.selectAll = false;

                let params = {
                    perPage: vm.pagination.per_page,
                    page: ( lastPage < vm.pagination.last_page ) ? 1 : vm.pagination.current_page, //lastPage in place of 1 is mental
                    orderBy: orderBy,
                    order: ( order == 1 ) ? 'asc' : 'desc',
                    trash: 1
                };

                if ( vm.search.length )
                    params.search = vm.search;

                progress.start();

                vm.$http.get(vm.resourceUrl, {params : params}).then(function(response) {
                    var subscribers = response.data.subscribers;
                    if ( subscribers && subscribers.data && subscribers.data.length ) {
                        vm.subscribers = subscribers.data;
                        vm.orderAttr = orderBy;
                        vm.orderToggle = order;

                        vm.$set(vm, 'pagination', {
                            total: subscribers.total,
                            per_page: subscribers.per_page,
                            current_page: subscribers.current_page,
                            last_page: subscribers.last_page,
                            from: subscribers.from,
                            to: subscribers.to
                        });

                        vm.subscriberIds = [];
                        _.forEach(subscribers.data, function(subscriber) {
                            vm.subscriberIds.push(subscriber.id);
                        });

                        progress.finish();
                        vm.successfulFetch = true;
                    }
                    else {
                        var type = vm.searching ? 'error' : 'info';
                        var title = vm.searching ? 'Computer says No!' : 'Computer says Yes!';
                        var message = vm.searching ? 'Your search returned no results. Please try again with different keywords' : 'Your recycle bin is nice and clean. Well done you!';

                        swal(title, message, type);
                        progress.finish();
                    }
                }, function(error) {
                    swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    progress.fail();
                });
            },
            destroySubscriber(subscriber) {
                var vm = this;
                var progress = vm.$Progress;

                swal({
                    title: "Permanently delete subscriber: " + subscriber.first_name + ' ' + subscriber.last_name + "?",
                    text: "This is irreversible",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Delete",
                }, function() {
                    progress.start();

                    vm.$http.delete(vm.resourceUrl + '/' + subscriber.id, { params : { destroy: 1 } }).then(function(response) {
                        if ( response.data && response.data.success ) {
                            progress.finish();
                            swal({ title: "Success", text: response.data.success, type: 'success', animation: 'slide-from-bottom'}, function() {
                                vm.fetchSubscribers();
                            });
                        }
                    }, function(error) {
                        progress.fail();

                        if ( error.status && error.status == 404 ) {
                            swal({ title: "An Error Occurred", text: 'The subscriber does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                                vm.$set(vm.pagination, 'total', vm.pagination.total - 1);
                                vm.fetchSubscribers();
                            });
                        }
                        else
                            swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    });
                });
            },
            quickEditSubscribers() {
                var vm = this;
                var action = _.toLower(vm.quickEdit);
                var selected = vm.selected;
                var progress = vm.$Progress;

                if ( action.length && selected.length ) {
                    if ( action == 'export' ) {
                        var urlString = '';

                        _.forEach(selected, function(id, index) {
                            var operand = index ? '&' : '?';
                            urlString += operand + 'subIds[]=' + id;
                        });

                        window.location = this.resourceUrl + '/export' + urlString;
                    }
                    else {
                        progress.start();

                        vm.$http.put(vm.resourceUrl + '/' + action + '/quick-edit', {subscribers: selected}).then(function (response) {
                            if (response.data && response.data.success) {
                                progress.finish();
                                vm.quickEdit = '';
                                swal({
                                    title: "Success",
                                    text: response.data.success,
                                    type: 'success',
                                    animation: 'slide-from-bottom'
                                }, function () {

                                    if (action == 'activate' || action == 'deactivate') { //Force them to see what they did!
                                        vm.orderAttr = 'updated_at';
                                        vm.orderToggle = -1;
                                    }
                                    vm.fetchSubscribers();
                                });
                            }
                        }, function (error) {
                            progress.fail();
                            vm.quickEdit = '';
                            var message = ( error && error.length ) ? error : 'Please refresh the page and try again.';
                            swal('An Error Occurred', message, 'error');
                        });
                    }

                }
            },
            exportSubscribers() {
                window.location = this.resourceUrl + '/export?trash=1';
            },
            changeSort(attr) {
                var orderToggle = ( this.orderAttr == attr ) ? this.orderToggle * -1 : 1;
                this.fetchSubscribers(attr, orderToggle);
            },
            getSortIcon(attr) {
                var icon = 'fa-sort';
                if ( this.orderAttr == attr )
                    icon = ( this.orderToggle == 1 ) ? 'fa-sort-asc' : 'fa-sort-desc';
                return icon;
            },
            hidePagination() {
                return ( _.ceil(this.pagination.total / this.pagination.per_page) == 1 )
            },
            activeIcon(activeAttr) {
                return activeAttr ? 'fa-check' : 'fa-times';
            },
            getSubscriberMLists(mailing_lists) {
                var mListsString = '(None)';
                var num = ( mailing_lists && _.isArray(mailing_lists) ) ? mailing_lists.length : 0;
                var mListsArr = [];

                if ( num ) {
                    _.forEach(mailing_lists, function(mList) {
                        mListsArr.push(mList.name);
                    });
                    mListsString = _.join(mListsArr.sort(), ' | ');
                }

                return mListsString;
            },
            getInitialPagination() {
                return {
                    total: 0,
                    per_page: ( userSubscribersSettings.paginate && userSubscribersSettings.paginate.length ) ? +userSubscribersSettings.paginate : 25,
                    current_page: 1,
                    last_page: 0,
                    from: 1,
                    to: ( userSubscribersSettings.paginate && userSubscribersSettings.paginate.length ) ? +userSubscribersSettings.paginate : 25
                }
            },
            initiateSearch() {
                this.searching = true;
                this.search = '';
            },
            doSearch() {
                if ( this.search.length ) {
                    this.subscribers = null;
                    this.pagination = this.getInitialPagination();
                    this.fetchSubscribers();
                }
            },
            cancelSearch() {
                this.searching = false;
                this.search = '';
                this.fetchSubscribers();
            }
        },
        watch: {
            selected() {
                this.quickEdit = '';
            },
            quickEdit(action) {
                var vm = this;
                if ( action.length && vm.selected.length ) {
                    swal({
                        title: _.capitalize(action) + " " + vm.selected.length + " Subscribers?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: _.capitalize(action),
                    }, function(confirmed) {
                        if ( confirmed )
                            vm.quickEditSubscribers();
                        else
                            vm.quickEdit = '';
                    });
                }
            }
        },
        filters: {
            localTime(date) {
                return moment(date + ' Z', 'YYYY-MM-DD HH:mm:ss Z', true).format('D MMM YYYY HH:mm');
            }
        }
    }
</script>
