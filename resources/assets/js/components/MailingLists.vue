<template xmlns="http://www.w3.org/1999/XSL/Transform" >
    <div class="mailing-lists">
        <vue-progress-bar></vue-progress-bar>
        <form v-on:submit.prevent='addMList' v-if='! editingMList' class='animated bounce'>
            <div class="form-group">
                <input class="form-control" placeholder="New Mailing List" v-model.trim="newMList.name">
            </div>
        </form>
        <form v-on:submit.prevent='updateMList' v-if='editingMList' v-on:keyup.esc="editingMList = ! editingMList" class='animated bounce'>
            <div class="form-group">
                <input class="form-control" placeholder="Mailing List" v-model.trim="editMList.name">
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
                    <th>Name <button v-on:click="changeSort('name')"><i v-bind:class="'fa ' + getSortIcon('name')"></i></button></th>
                    <th>Subscribers Count <button v-on:click="changeSort('subscribers_count')"><i v-bind:class="'fa ' + getSortIcon('subscribers_count')"></i></button></th>
                    <th>Created <button v-on:click="changeSort('created_at')"><i v-bind:class="'fa ' + getSortIcon('created_at')"></i></button></th>
                    <th>Updated <button v-on:click="changeSort('updated_at')"><i v-bind:class="'fa ' + getSortIcon('updated_at')"></i></button></th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="mList in orderedMLists">
                    <td>{{ mList.name }}</td>
                    <td>{{ mList.subscribers_count }}</td>
                    <td>{{ mList.created_at | localTime }}</td>
                    <td>{{ mList.updated_at | localTime }}</td>
                    <td><i class="fa fa-pencil-square-o btn btn-default btn-xs" v-on:click="fetchMList(mList)"></i></td>
                    <td><i class="fa fa-times btn btn-danger btn-xs" v-on:click="deleteMList(mList)"></i></td>
                </tr>
            </tbody>
        </table>
        <pagination :pagination="pagination" :callback="fetchMLists" :options="paginationOptions"></pagination>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = 'mailing-lists';
                this.fetchMLists();
            });
        },
        data() {
            return {
                newMList: { name: ''},
                mLists: [],
                orderToggle: ( userMailingListsSettings.order && userMailingListsSettings.order == 'asc' ) ? 1 : -1,
                orderAttr: ( userMailingListsSettings.order_by && userMailingListsSettings.order_by.length ) ? userMailingListsSettings.order_by : 'updated_at',
                defaultOrderAttr: 'updated_at',
                pagination: {
                    total: 25,
                    per_page: ( userMailingListsSettings.paginate && userMailingListsSettings.paginate.length) ? userMailingListsSettings.paginate : 25,
                    current_page: 1,
                    last_page: 1,
                    from: 1,
                    to: 25
                },
                paginationOptions: {
                    offset: 5,
                    alwaysShowPrevNext: true
                },
                perPageOptions: [
                    { text: '10', value: 10},
                    { text: '25', value: 25},
                    { text: '50', value: 50},
                    { text: '100', value: 100},
                    { text: '500', value: 500}
                ],
                editingMList: false,
                editMList: {}
            }
        },
        computed: {
            orderedMLists() {
                return _.orderBy(this.mLists, [this.orderAttr, this.defaultOrderAttr], [( this.orderToggle == 1 ) ? 'asc' : 'desc', 'desc']);
            }
        },
        methods: {
            fetchMLists(orderAttr, orderToggle) {
                var vm = this;
                var orderBy = orderAttr ? orderAttr : vm.orderAttr;
                var order = orderToggle ? orderToggle : vm.orderToggle;
                var progress = vm.$Progress;
                var lastPage = Math.ceil(vm.pagination.total / vm.pagination.per_page);

                let params = {
                    perPage: vm.pagination.per_page,
                    page: ( lastPage < vm.pagination.last_page ) ? lastPage : vm.pagination.current_page,
                    orderBy: orderBy,
                    order: ( order == 1 ) ? 'asc' : 'desc'
                };

                progress.start();

                vm.$http.get(vm.resourceUrl, {params : params}).then(function(response) {
                    if ( response.data && response.data.data && response.data.data.length ) {
                        vm.mLists = response.data.data;
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
                        swal('Computer says no', "You don't have any mailing lists yet. Please add some", 'error');
                        progress.finish();
                    }
                }, function(error) {
                    swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    progress.fail();
                });
            },
            changeSort(attr) {
                var orderToggle = ( this.orderAttr == attr ) ? this.orderToggle * -1 : 1;
                this.fetchMLists(attr, orderToggle);
            },
            getSortIcon(attr) {
                var icon = 'fa-sort';
                if ( this.orderAttr == attr )
                    icon = ( this.orderToggle == 1 ) ? 'fa-sort-asc' : 'fa-sort-desc';
                return icon;
            },
            addMList() {
                var vm = this;
                var progress = vm.$Progress;
                var newMListName = vm.newMList.name.trim();

                if ( newMListName ) {
                    progress.start();

                    vm.$http.post(vm.resourceUrl, vm.newMList).then(function(response) {
                        swal({ title: "Success", text: 'Mailing List created', type: 'success', animation: 'slide-from-bottom', timer: 3000 });

                        vm.newMList = { name: '' };
                        progress.finish();
                        vm.fetchMLists();
                    }, function(error) {
                        if ( error.status && error.status == 422 && error.data.name ) {
                            swal('An Error Occurred', error.data.name, 'error');
                        }
                        else {
                            swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                        }
                        progress.fail();
                    });
                }
            },
            hidePagination() {
                return ( Math.ceil(this.pagination.total / this.pagination.per_page) == 1 )
            },
            fetchMList(mList) {
                var vm = this;
                if ( mList && mList.id ) {
                    var progress = vm.$Progress;
                    progress.start();

                    vm.$http.get(vm.resourceUrl + '/' + mList.id).then(function (response) {
                        if (response.data && response.status == 200) {
                            progress.finish();
                            vm.editingMList = true;
                            vm.editMList = response.data;
                        }
                    }, function (error) {
                        swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                        progress.fail();
                    });
                }
                else
                    swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
            },
            updateMList() {
                var vm = this;
                var progress = vm.$Progress;
                var editMListName = vm.editMList.name.trim();

                if ( editMListName ) {
                    progress.start();

                    vm.$http.put(vm.resourceUrl + '/' + vm.editMList.id, vm.editMList).then(function(response) {
                        swal({ title: "Success", text: 'Mailing List updated', type: 'success', animation: 'slide-from-bottom', timer: 3000 });

                        vm.editMList = {};
                        vm.editingMList = false;
                        progress.finish();
                        vm.fetchMLists();
                    }, function(error) {
                        if ( error.status && error.status == 422 && error.data.name ) {
                            swal('An Error Occurred', error.data.name, 'error');
                        }
                        else if ( error.status && error.status == 404 ) {
                            swal({ title: "An Error Occurred", text: 'The mailing list does not exist', type: 'error', animation: 'slide-from-top', timer: 3000 });
                        }
                        else {
                            swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                        }
                        progress.fail();
                    });
                }
            },
            deleteMList(mList) {
                var vm = this;
                var progress = this.$Progress;

                swal({
                    title: "Delete mailing list: " + mList.name + "?",
                    text: "You will not be able to recover this mailing list. Subscribers won't be deleted.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Delete",
                    closeOnConfirm: false
                }, function() {
                    progress.start();

                    vm.$http.delete(vm.resourceUrl + '/' + mList.id).then(function(response) {
                        if ( response.data && response.data.success ) {
                            swal({ title: "Success", text: response.data.success, type: 'success', animation: 'slide-from-bottom', timer: 3000 });

                            vm.$set(vm.pagination, 'total', vm.pagination.total - 1);
                            progress.finish();
                            vm.fetchMLists();
                        }
                    }, function(error) {
                        progress.fail();
                        if ( error.status && error.status == 404 ) {
                            swal({ title: "An Error Occurred", text: 'The mailing list does not exist', type: 'error', animation: 'slide-from-top', timer: 3000 });
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