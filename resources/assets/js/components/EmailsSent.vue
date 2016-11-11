<template>
    <div class="emails-sent" v-if="successfulFetch" v-cloak>
        <div class="clearfix" style="margin: 20px 0;">
            <form v-on:submit.prevent="doSearch">
                <input type="text" v-model.trim="search" placeholder="Search" />
                <a v-on:click.prevent="cancelSearch" href="" title="Cancel Search" v-if="searching"><i class="fa fa-ban"></i></a>
            </form>
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
                    <th>Subject <button v-on:click="changeSort('subject')"><i v-bind:class="'fa ' + getSortIcon('subject')"></i></button></th>
                    <th>Sender <button v-on:click="changeSort('from')"><i v-bind:class="'fa ' + getSortIcon('from')"></i></button></th>
                    <th>User</th>
                    <th>Edition</th>
                    <th>Sent <button v-on:click="changeSort('sent_at')"><i v-bind:class="'fa ' + getSortIcon('sent_at')"></i></button></th>
                    <th>Created <button v-on:click="changeSort('created_at')"><i v-bind:class="'fa ' + getSortIcon('created_at')"></i></button></th>
                    <th>Updated <button v-on:click="changeSort('updated_at')"><i v-bind:class="'fa ' + getSortIcon('updated_at')"></i></button></th>
                    <th colspan="5"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="email in orderedEmails">
                    <td><input type="checkbox" v-model="selected" v-bind:value="email.id"></td>
                    <td>{{ email.subject }}</td>
                    <td>{{ email.from }}</td>
                    <td>{{ email.user.first_name }} {{ email.user.last_name }}</td>
                    <td>{{ email.email_edition.edition }}</td>
                    <td>{{ email.sent_at | localTime }}</td>
                    <td>{{ email.created_at | localTime }}</td>
                    <td>{{ email.updated_at | localTime }}</td>
                    <td><a v-on:click.prevent="exportEmail(email.id)" href="" title="PDF"><i class="fa fa-arrow-circle-down"></i></a></td>
                    <td><router-link v-bind:to="{ name: 'emails.forward', params: { id: email.id } }" title="Forward" class="btn btn-default btn-xs"><i class="fa fa-long-arrow-right"></i></router-link></td>
                    <td><router-link v-bind:to="{ name: 'emails.stats', params: { id: email.id } }" title="Stats" class="btn btn-default btn-xs" v-if="email.send_success"><i class="fa fa-bar-chart"></i></router-link></td>
                    <td></td>
                    <td><i class="fa fa-times btn btn-danger btn-xs" v-on:click="deleteEmail(email)"></i></td>
                </tr>
            </tbody>
        </table>
        <pagination :pagination="pagination" :callback="fetchEmails" :options="paginationOptions"></pagination>
    </div>
</template>
<script>
    export default{
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = emailsLinks.baseUri;
                this.pagination = this.getInitialPagination();
                this.fetchEmails();
            });
        },
        data() {
            return {
                emails: [],
                orderToggle: ( userEmailsSettings.order && userEmailsSettings.order == 'asc' ) ? 1 : -1,
                orderAttr: ( userEmailsSettings.order_by && userEmailsSettings.order_by.length ) ? userEmailsSettings.order_by : 'updated_at',
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
                    { text: 'Delete', value: 'delete' }
                ],
                quickEdit: '',
                emailIds: [],
                selected: [],
                successfulFetch: false,
                search: '',
                searching: false
            }
        },
        computed: {
            orderedEmails() {
                return _.orderBy(this.emails, [this.orderAttr, this.defaultOrderAttr], [( this.orderToggle == 1 ) ? 'asc' : 'desc', 'desc']);
            },
            selectAll: {
                get() {
                    return this.emailIds ? this.selected.length == this.emailIds.length : false;
                },
                set(value) {
                    this.selected = value ? this.emailIds : [];
                }
            }
        },
        methods: {
            fetchEmails(orderAttr, orderToggle) {
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
                    order: ( order == 1 ) ? 'asc' : 'desc'
                };

                if ( vm.search.length )
                    params.search = vm.search;

                progress.start();

                vm.$http.get(vm.resourceUrl, {params : params}).then(function(response) {
                    var emails = response.data.emails;

                    if ( emails && emails.data && emails.data.length ) {
                        vm.emails = emails.data;
                        vm.orderAttr = orderBy;
                        vm.orderToggle = order;

                        vm.$set(vm, 'pagination', {
                            total: emails.total,
                            per_page: emails.per_page,
                            current_page: emails.current_page,
                            last_page: emails.last_page,
                            from: emails.from,
                            to: emails.to
                        });

                        vm.emailIds = [];
                        _.forEach(emails.data, function(email) {
                           vm.emailIds.push(email.id);
                        });

                        progress.finish();
                        vm.successfulFetch = true;
                    }
                    else {
                        var message = vm.searching ? 'Your search returned no results. Please try again with different keywords' : 'You haven\'t sent any emails yet. Sent emails will appear here';

                        swal({ title: "Computer says no", text: message, type: 'error', animation: 'slide-from-top'}, function() {});
                        vm.emails = [];
                        progress.fail();
                    }
                }, function(error) {
                    swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    progress.fail();
                });
            },
            deleteEmail(email) {
                var vm = this;
                var progress = vm.$Progress;

                swal({
                    title: "Delete email: " + email.subject + "?",
                    text: "The email will be moved to trash.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Delete",
                }, function() {
                    progress.start();

                    vm.$http.delete(vm.resourceUrl + '/' + email.id).then(function(response) {
                        if ( response.data && response.data.success ) {
                            progress.finish();
                            swal({ title: "Success", text: response.data.success, type: 'success', animation: 'slide-from-bottom'}, function() {
                                vm.fetchEmails();
                            });
                        }
                    }, function(error) {
                        progress.fail();

                        if ( error.status && error.status == 404 ) {
                            swal({ title: "An Error Occurred", text: 'The email does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                                vm.$set(vm.pagination, 'total', vm.pagination.total - 1);
                                vm.fetchEmails();
                            });
                        }
                        else
                            swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    });
                });
            },
            quickEditEmails() {
                var vm = this;
                var action = _.toLower(vm.quickEdit);
                var selected = vm.selected;
                var progress = vm.$Progress;

                if ( action.length && selected.length ) {
                    progress.start();

                    vm.$http.put(vm.resourceUrl + '/' + action + '/quick-edit', {emails: selected}).then(function (response) {
                        if (response.data && response.data.success) {
                            progress.finish();
                            vm.quickEdit = '';
                            swal({
                                title: "Success",
                                text: response.data.success,
                                type: 'success',
                                animation: 'slide-from-bottom'
                            }, function () {
                                vm.fetchEmails();
                            });
                        }
                    }, function (error) {
                        progress.fail();
                        vm.quickEdit = '';
                        var message = ( error.data && error.data.error ) ? error.data.error : 'Please refresh the page and try again.';
                        swal('An Error Occurred', message, 'error');
                    });
                }
            },
            exportEmail(id) {
                window.open(this.resourceUrl + '/export?id=' + id);
            },
            changeSort(attr) {
                var orderToggle = ( this.orderAttr == attr ) ? this.orderToggle * -1 : 1;
                this.fetchEmails(attr, orderToggle);
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
            getInitialPagination() {
                return {
                    total: 0,
                    per_page: ( userEmailsSettings.paginate && userEmailsSettings.paginate.length ) ? +userEmailsSettings.paginate : 25,
                    current_page: 1,
                    last_page: 0,
                    from: 1,
                    to: ( userEmailsSettings.paginate && userEmailsSettings.paginate.length ) ? +userEmailsSettings.paginate : 25
                };
            },
            doSearch() {
                if ( this.search.length ) {
                    this.emails = null;
                    this.pagination = this.getInitialPagination();
                    this.fetchEmails();
                }
            },
            cancelSearch() {
                this.search = '';
                this.fetchEmails();
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
                        title: _.capitalize(action) + " " + vm.selected.length + " emails?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: _.capitalize(action),
                    }, function(confirmed) {
                        if ( confirmed )
                            vm.quickEditEmails();
                        else
                            vm.quickEdit = '';
                    });
                }
            },
            '$route'(to) {
                this.fetchEmails();
            },
            search(newVal) {
                this.searching = newVal.length;
            }
        },
        filters: {
            localTime(date) {
                return moment(date + ' Z', 'YYYY-MM-DD HH:mm:ss Z', true).format('D MMM YYYY HH:mm');
            }
        }
    }
</script>
