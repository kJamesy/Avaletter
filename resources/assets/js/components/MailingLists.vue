<template xmlns="http://www.w3.org/1999/XSL/Transform" >
    <vue-progress-bar></vue-progress-bar>
    <div class="mailing-lists" >
        <form v-on:submit='addMList' v-if='! editingMList'>
            <div class="form-group">
                <input class="form-control" placeholder="New Mailing List" v-model="newMList.name">
            </div>
            <!--<button class="btn btn-default">Create</button>-->
        </form>
        <form v-on:submit='updateMList' v-if='editingMList'>
            <div class="form-group">
                <input class="form-control" placeholder="Mailing List" v-model="editMList.name">
            </div>
            <!--<button class="btn btn-default">Create</button>-->
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
                <th>Name <button @click="changeSort('name')"><i class="fa {{ getSortIcon('name') }}"></i></button></th>
                <th>Created <button @click="changeSort('created_at')"><i class="fa {{ getSortIcon('created_at') }}"></i></button></th>
                <th>Updated <button @click="changeSort('updated_at')"><i class="fa {{ getSortIcon('updated_at') }}"></i></button></th>
                <th colspan="2"></th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="mList in mLists | orderBy orderAttr orderToggle">
                <td>{{ mList.name }}</td>
                <td>{{ mList.created_at | localTime }}</td>
                <td>{{ mList.updated_at | localTime }}</td>
                <td><i class="fa fa-pencil-square-o btn btn-default btn-xs" @click="fetchMList(mList)"></i></td>
                <td><i class="fa fa-times btn btn-danger btn-xs" @click="deleteMList(mList)"></i></td>
            </tr>
            </tbody>
        </table>
        <pagination :pagination="pagination" :callback="fetchMLists" :offset="5" v-show="! hidePagination()"></pagination>
    </div>
    <!--{{ mLists | json }}-->
</template>

<script>
    export default {
        data: function() {
            return {
                newMList: { name: ''},
                mLists: [],
                orderToggle: -1,
                orderAttr: 'created_at',
                pagination: {
                    total: 25,
                    per_page: 10,
                    current_page: 1,
                    last_page: 1,
                    from: 1,
                    to: 25
                },
                perPageOptions: [
                    { text: '10', value: 10},
                    { text: '25', value: 25},
                    { text: '50', value: 50},
                    { text: '100', value: 100},
                    { text: '1000', value: 1000}
                ],
                editingMList: false,
                editMList: {}
            }
        },
        ready: function () {
            this.fetchMLists();
            this.resourceUrl = 'mailing-lists';

        },
        methods: {
            fetchMLists: function (orderAttr, orderToggle) {
                var orderBy = orderAttr ? orderAttr : this.orderAttr;
                var order = orderToggle ? orderToggle : this.orderToggle;
                var progress = this.$Progress;
                var mLists = [];
                var lastPage = Math.ceil(this.pagination.total / this.pagination.per_page);

                let params = {
                    perPage: this.pagination.per_page,
                    page: ( lastPage < this.pagination.last_page ) ? lastPage : this.pagination.current_page,
                    orderBy: orderBy,
                    order: ( order == 1 ) ? 'asc' : 'desc'
                };

                progress.start();

                this.$http.get(this.resourceUrl, {params : params}).then(function(response) {
                    if ( response.data && response.data.data && response.data.data.length ) {
                        this.$set('mLists', response.data.data);
                        this.orderAttr = orderBy;
                        this.orderToggle = order;

                        var pagination = {
                            total: response.data.total,
                            per_page: response.data.per_page,
                            current_page: response.data.current_page,
                            last_page: response.data.last_page,
                            from: response.data.from,
                            to: response.data.to
                        };
                        this.$set('pagination', pagination);
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
            changeSort: function(attr) {
                var orderToggle = ( this.orderAttr == attr ) ? this.orderToggle * -1 : 1;
                this.fetchMLists(attr, orderToggle);
            },
            getSortIcon: function(attr) {
                var icon = 'fa-sort';
                if ( this.orderAttr == attr )
                    icon = ( this.orderToggle == 1 ) ? 'fa-sort-asc' : 'fa-sort-desc';
                return icon;
            },
            addMList: function (e) {
                e.preventDefault();
                var that = this;
                var progress = that.$Progress;
                var newMListName = that.newMList.name.trim();

                if ( newMListName ) {
                    progress.start();
                    that.newMList.name = newMListName;

                    that.$http.post(that.resourceUrl, that.newMList).then(function(response) {
                        swal({
                            title: "Success",
                            text: 'Mailing List created',
                            type: 'success',
                            animation: 'slide-from-bottom',
                            timer: 3000
                        });

                        that.newMList = { name: '' };
                        progress.finish();
                        that.fetchMLists();
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
            hidePagination: function() {
                return ( Math.ceil(this.pagination.total / this.pagination.per_page) == 1 )
            },
            fetchMList: function(mList) {
                if ( mList && mList.id ) {
                    var progress = this.$Progress;
                    progress.start();

                    this.$http.get(this.resourceUrl + '/' + mList.id).then(function (response) {
                        if (response.data && response.status == 200) {
                            progress.finish();
                            this.editingMList = true;
                            this.editMList = response.data;
                        }
                    }, function (error) {
                        swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                        progress.fail();
                    });
                }
                else
                    swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
            },
            updateMList: function(e) {
                e.preventDefault();
                var that = this;
                var progress = that.$Progress;
                var editMListName = that.editMList.name.trim();

                if ( editMListName ) {
                    progress.start();
                    that.editMList.name = editMListName;

                    that.$http.put(that.resourceUrl + '/' + that.editMList.id, that.editMList).then(function(response) {
                        swal({
                            title: "Success",
                            text: 'Mailing List updated',
                            type: 'success',
                            animation: 'slide-from-bottom',
                            timer: 3000
                        });

                        that.editMList = {};
                        that.editingMList = false;
                        progress.finish();
                        that.fetchMLists();
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
            deleteMList: function(mList) {
                var that = this;
                var progress = this.$Progress;

                swal({
                            title: "Delete mailing list: " + mList.name + "?",
                            text: "You will not be able to recover this mailing list. Subscribers won't be deleted.",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Delete!",
                            closeOnConfirm: false
                        },
                        function() {
                            progress.start();

                            that.$http.delete(that.resourceUrl + '/' + mList.id).then(function(response) {
                                if ( response.data && response.data.success ) {
                                    swal({
                                        title: "Success",
                                        text: response.data.success,
                                        type: 'success',
                                        animation: 'slide-from-bottom',
                                        timer: 3000
                                    });

                                    progress.finish();
                                    that.fetchMLists();
                                }
                            }, function(error) {
                                if ( error.data && error.data.error )
                                    swal('An Error Occurred', error.data.error, 'error');
                                else
                                    swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                                progress.fail();
                            });
                        });
            }
        },
        filters: {
            localTime: function (date) {
                return moment(date + ' Z', 'YYYY-MM-DD HH:mm:ss Z', true).format('D MMM YYYY HH:mm');
            }
        }
    }
</script>