<template xmlns="http://www.w3.org/1999/XSL/Transform" >
    <vue-progress-bar></vue-progress-bar>
    <div class="mailing-lists" >
        <form v-on:submit='addMList'>
            <div class="form-group">
                <input class="form-control" placeholder="New Mailing List" v-model="newMList.name">
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
                </tr>
            </thead>
            <tbody>
                <tr v-for="mList in mLists | orderBy orderAttr orderToggle">
                    <td>{{ mList.name }}</td>
                    <td>{{ mList.created_at | localTime }}</td>
                    <td>{{ mList.updated_at | localTime }}</td>
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
                ]
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
                var progress = this.$Progress;
                var newMListName = this.newMList.name.trim();

                if ( newMListName ) {
                    progress.start();
                    this.newMList.name = newMListName;
                    var newMList = this.newMList;

                    this.$http.post(this.resourceUrl, newMList).then(function(response) {
                        swal({
                            title: "Success",
                            text: 'Mailing List created',
                            type: 'success',
                            animation: 'slide-from-bottom',
                            timer: 3000
                        });

                        newMList = { name: '' };
                        progress.finish();
                        this.fetchMLists();
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
            }
        },
        filters: {
            localTime: function (date) {
               return moment(date + ' Z', 'YYYY-MM-DD HH:mm:ss Z', true).format('D MMM YYYY HH:mm');
            }
        }
    }
</script>