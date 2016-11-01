<template xmlns="http://www.w3.org/1999/XSL/Transform" >
    <div class="email-editions">
        <vue-progress-bar></vue-progress-bar>
        <form v-on:submit.prevent='addEdition' v-if='! editingEdition' class='animated bounce'>
            <div class="form-group">
                <input class="form-control" placeholder="New email edition" v-model.trim="newEdition.edition">
            </div>
        </form>
        <form v-on:submit.prevent='updateEdition' v-if='editingEdition' v-on:keyup.esc="editingEdition = ! editingEdition" class='animated bounce'>
            <div class="form-group">
                <input class="form-control" placeholder="Email edition" v-model.trim="editEdition.edition">
            </div>
        </form>

        <div style="float: right; margin: 20px 0;">
            <label for="records_per_page">Records Per Page</label>
            <select v-model="pagination.per_page" id="records_per_page" >
                <option v-for="option in perPageOptions" v-bind:value="option.value">
                    {{ option.text }}
                </option>
            </select>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Name <button v-on:click="changeSort('edition')"><i v-bind:class="'fa ' + getSortIcon('edition')"></i></button></th>
                    <th>Email Count <button v-on:click="changeSort('emails_count')"><i v-bind:class="'fa ' + getSortIcon('emails_count')"></i></button></th>
                    <th>Created <button v-on:click="changeSort('created_at')"><i v-bind:class="'fa ' + getSortIcon('created_at')"></i></button></th>
                    <th>Updated <button v-on:click="changeSort('updated_at')"><i v-bind:class="'fa ' + getSortIcon('updated_at')"></i></button></th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="edition in orderedEditions">
                    <td>{{ edition.edition }}</td>
                    <td><a class="btn btn-default btn-xs" v-bind:href="emailsBaseUrl + '/' + edition.id + '/email-edition'">{{ edition.emails_count }}</a></td>
                    <td>{{ edition.created_at | localTime }}</td>
                    <td>{{ edition.updated_at | localTime }}</td>
                    <td><i class="fa fa-pencil-square-o btn btn-default btn-xs" v-on:click="fetchEdition(edition)"></i></td>
                    <td><i class="fa fa-times btn btn-danger btn-xs" v-on:click="deleteEdition(edition)"></i></td>
                </tr>
            </tbody>
        </table>
        <pagination :pagination="pagination" :callback="fetchEditions" :options="paginationOptions"></pagination>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = emailEditionsLinks.baseUri;
                this.emailsBaseUrl = emailEditionsLinks.emailsBaseUri;
                this.fetchEditions();
            });
        },
        data() {
            return {
                newEdition: { edition: ''},
                editions: [],
                orderToggle: ( userEmailEditionsSettings.order && userEmailEditionsSettings.order == 'asc' ) ? 1 : -1,
                orderAttr: ( userEmailEditionsSettings.order_by && userEmailEditionsSettings.order_by.length ) ? userEmailEditionsSettings.order_by : 'updated_at',
                defaultOrderAttr: 'updated_at',
                pagination: {
                    total: 25,
                    per_page: ( userEmailEditionsSettings.paginate && userEmailEditionsSettings.paginate.length) ? +userEmailEditionsSettings.paginate : 25,
                    current_page: 1,
                    last_page: 1,
                    from: 1,
                    to: ( userEmailEditionsSettings.paginate && userEmailEditionsSettings.paginate.length) ? +userEmailEditionsSettings.paginate : 25,
                },
                paginationOptions: {
                    offset: 5,
                    alwaysShowPrevNext: true
                },
                perPageOptions: [
                    { text: '10', value: 10 },
                    { text: '25', value: 25 },
                    { text: '50', value: 50 },
                    { text: '100', value: 100 },
                    { text: '500', value: 500 }
                ],
                editingEdition: false,
                editEdition: {}
            }
        },
        computed: {
            orderedEditions() {
                return _.orderBy(this.editions, [this.orderAttr, this.defaultOrderAttr], [( this.orderToggle == 1 ) ? 'asc' : 'desc', 'desc']);
            }
        },
        methods: {
            fetchEditions(orderAttr, orderToggle) {
                var vm = this;
                var orderBy = orderAttr ? orderAttr : vm.orderAttr;
                var order = orderToggle ? orderToggle : vm.orderToggle;
                var progress = vm.$Progress;
                var lastPage = Math.ceil(vm.pagination.total / vm.pagination.per_page);

                let params = {
                    perPage: vm.pagination.per_page,
                    page: ( lastPage < vm.pagination.last_page ) ? 1 : vm.pagination.current_page, //lastPage in place of 1 is mental
                    orderBy: orderBy,
                    order: ( order == 1 ) ? 'asc' : 'desc'
                };

                progress.start();

                vm.$http.get(vm.resourceUrl, {params : params}).then(function(response) {
                    if ( response.data && response.data.data && response.data.data.length ) {
                        vm.editions = response.data.data;
                        vm.orderAttr = orderBy;
                        vm.orderToggle = order;

                        vm.pagination = {
                            total: response.data.total,
                            per_page: response.data.per_page,
                            current_page: response.data.current_page,
                            last_page: response.data.last_page,
                            from: response.data.from,
                            to: response.data.to
                        };
                        progress.finish();
                    }
                    else {
                        swal('Computer says no', "You don't have any email editions yet. Please add some", 'error');
                        progress.finish();
                    }
                }, function(error) {
                    swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    progress.fail();
                });
            },
            changeSort(attr) {
                var orderToggle = ( this.orderAttr == attr ) ? this.orderToggle * -1 : 1;
                this.fetchEditions(attr, orderToggle);
            },
            getSortIcon(attr) {
                var icon = 'fa-sort';
                if ( this.orderAttr == attr )
                    icon = ( this.orderToggle == 1 ) ? 'fa-sort-asc' : 'fa-sort-desc';
                return icon;
            },
            addEdition() {
                var vm = this;
                var progress = vm.$Progress;

                if ( vm.newEdition.edition.trim() ) {
                    progress.start();

                    vm.$http.post(vm.resourceUrl, vm.newEdition).then(function(response) {
                        swal({ title: "Success", text: 'Email edition created', type: 'success', animation: 'slide-from-bottom', timer: 3000 });

                        vm.newEdition = { edition: '' };
                        progress.finish();
                        vm.fetchEditions();
                    }, function(error) {
                        var message = ( error.status && error.status == 422 && error.data.edition ) ? error.data.edition : 'Please refresh the page and try again.';
                        swal('An Error Occurred', message, 'error');
                        progress.fail();
                    });
                }
            },
            hidePagination() {
                return ( Math.ceil(this.pagination.total / this.pagination.per_page) == 1 )
            },
            fetchEdition(edition) {
                var vm = this;
                if ( edition && edition.id ) {
                    var progress = vm.$Progress;
                    progress.start();

                    vm.$http.get(vm.resourceUrl + '/' + edition.id).then(function (response) {
                        if (response.data && response.status == 200) {
                            progress.finish();
                            vm.editingEdition = true;
                            vm.editEdition = response.data;
                        }
                    }, function (error) {
                        swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                        progress.fail();
                    });
                }
                else
                    swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
            },
            updateEdition() {
                var vm = this;
                var progress = vm.$Progress;

                if ( vm.editEdition.edition.trim() ) {
                    progress.start();

                    vm.$http.put(vm.resourceUrl + '/' + vm.editEdition.id, vm.editEdition).then(function(response) {
                        swal({ title: "Success", text: 'Email edition updated', type: 'success', animation: 'slide-from-bottom', timer: 3000 });

                        vm.editEdition = {};
                        vm.editingEdition = false;
                        progress.finish();
                        vm.fetchEditions();
                    }, function(error) {
                        if ( error.status && error.status == 422 && error.data.edition )
                            swal('An Error Occurred', error.data.edition, 'error');
                        else if ( error.status && error.status == 404 )
                            swal({ title: "An Error Occurred", text: 'The mailing list does not exist', type: 'error', animation: 'slide-from-top', timer: 3000 });
                        else
                            swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                        progress.fail();
                    });
                }
            },
            deleteEdition(edition) {
                var vm = this;
                var progress = this.$Progress;

                swal({
                    title: "Delete email edition: " + edition.edition + "?",
                    text: "You will not be able to recover this email edition. Emails won't be deleted.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Delete",
                    closeOnConfirm: false
                }, function() {
                    progress.start();

                    vm.$http.delete(vm.resourceUrl + '/' + edition.id).then(function(response) {
                        if ( response.data && response.data.success ) {
                            swal({ title: "Success", text: response.data.success, type: 'success', animation: 'slide-from-bottom', timer: 3000 });

                            vm.$set(vm.pagination, 'total', vm.pagination.total - 1);
                            progress.finish();
                            vm.fetchEditions();
                        }
                    }, function(error) {
                        progress.fail();
                        if ( error.status && error.status == 404 ) {
                            swal({ title: "An Error Occurred", text: 'The email edition does not exist', type: 'error', animation: 'slide-from-top', timer: 3000 });
                        }
                        else
                            swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    });
                });
            }
        },
        filters: {
            localTime(date) {
                return moment(date + ' Z', 'YYYY-MM-DD HH:mm:ss Z', true).format('D MMM YYYY HH:mm');
            }
        }
    }
</script>