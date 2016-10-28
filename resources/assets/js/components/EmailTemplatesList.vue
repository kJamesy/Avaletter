<template>
    <div class="templates-list" v-if="successfulFetch" v-cloak>
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
                    <th>Name <button v-on:click="changeSort('name')"><i v-bind:class="'fa ' + getSortIcon('name')"></i></button></th>
                    <th>Last Edited By<button v-on:click="changeSort('last_edited_by')"><i v-bind:class="'fa ' + getSortIcon('last_edited_by')"></i></button></th>
                    <th>Created <button v-on:click="changeSort('created_at')"><i v-bind:class="'fa ' + getSortIcon('created_at')"></i></button></th>
                    <th>Updated <button v-on:click="changeSort('updated_at')"><i v-bind:class="'fa ' + getSortIcon('updated_at')"></i></button></th>
                    <th colspan="4"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="template in orderedTemplates">
                    <td><input type="checkbox" v-model="selected" v-bind:value="template.id"></td>
                    <td>{{ template.name }}</td>
                    <td>{{ template.last_edited_by }}</td>
                    <td>{{ template.created_at | localTime }}</td>
                    <td>{{ template.updated_at | localTime }}</td>
                    <td><a v-on:click.prevent="exportTemplate(template.id)" href="" title="PDF"><i class="fa fa-arrow-circle-down"></i></a></td>
                    <td><router-link v-bind:to="{ name: 'emailTemplates.edit', params: { id: template.id } }" class="btn btn-default btn-xs"><i class="fa fa-pencil-square-o"></i></router-link></td>
                    <td></td>
                    <td><i class="fa fa-times btn btn-danger btn-xs" v-on:click="deleteTemplate(template)"></i></td>
                </tr>
            </tbody>
        </table>
        <pagination :pagination="pagination" :callback="fetchTemplates" :options="paginationOptions"></pagination>
    </div>
</template>
<script>
    export default{
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = emailTemplatesLinks.baseUri;
                this.pagination = this.getInitialPagination();
                this.fetchTemplates();
            });
        },
        data() {
            return {
                emailTemplates: [],
                orderToggle: ( userEmailTemplatesSettings.order && userEmailTemplatesSettings.order == 'asc' ) ? 1 : -1,
                orderAttr: ( userEmailTemplatesSettings.order_by && userEmailTemplatesSettings.order_by.length ) ? userEmailTemplatesSettings.order_by : 'updated_at',
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
                templateIds: [],
                selected: [],
                successfulFetch: false,
                search: '',
                searching: false
            }
        },
        computed: {
            orderedTemplates() {
                return _.orderBy(this.emailTemplates, [this.orderAttr, this.defaultOrderAttr], [( this.orderToggle == 1 ) ? 'asc' : 'desc', 'desc']);
            },
            selectAll: {
                get() {
                    return this.templateIds ? this.selected.length == this.templateIds.length : false;
                },
                set(value) {
                    this.selected = value ? this.templateIds : [];
                }
            }
        },
        methods: {
            fetchTemplates(orderAttr, orderToggle) {
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
                    var emailTemplates = response.data.emailTemplates;

                    if ( emailTemplates && emailTemplates.data && emailTemplates.data.length ) {
                        vm.emailTemplates = emailTemplates.data;
                        vm.orderAttr = orderBy;
                        vm.orderToggle = order;

                        vm.$set(vm, 'pagination', {
                            total: emailTemplates.total,
                            per_page: emailTemplates.per_page,
                            current_page: emailTemplates.current_page,
                            last_page: emailTemplates.last_page,
                            from: emailTemplates.from,
                            to: emailTemplates.to
                        });

                        vm.templateIds = [];
                        _.forEach(emailTemplates.data, function(emailTemplate) {
                           vm.templateIds.push(emailTemplate.id);
                        });

                        progress.finish();
                        vm.successfulFetch = true;
                    }
                    else {
                        var message = vm.searching ? 'Your search returned no results. Please try again with different keywords' : 'You don\'t have any email templates yet. Please add some';

                        swal({ title: "Computer says no", text: message, type: 'error', animation: 'slide-from-top'}, function() {});
                        progress.fail();
                    }
                }, function(error) {
                    swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    progress.fail();
                });
            },
            deleteTemplate(emailTemplate) {
                var vm = this;
                var progress = vm.$Progress;

                swal({
                    title: "Delete template: " + emailTemplate.name + "?",
                    text: "The email template will be permanently deleted.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Delete",
                }, function() {
                    progress.start();

                    vm.$http.delete(vm.resourceUrl + '/' + emailTemplate.id).then(function(response) {
                        if ( response.data && response.data.success ) {
                            progress.finish();
                            swal({ title: "Success", text: response.data.success, type: 'success', animation: 'slide-from-bottom'}, function() {
                                vm.fetchTemplates();
                            });
                        }
                    }, function(error) {
                        progress.fail();

                        if ( error.status && error.status == 404 ) {
                            swal({ title: "An Error Occurred", text: 'The email template does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                                vm.$set(vm.pagination, 'total', vm.pagination.total - 1);
                                vm.fetchTemplates();
                            });
                        }
                        else
                            swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    });
                });
            },
            quickEditTemplates() {
                var vm = this;
                var action = _.toLower(vm.quickEdit);
                var selected = vm.selected;
                var progress = vm.$Progress;

                if ( action.length && selected.length ) {
                    progress.start();

                    vm.$http.put(vm.resourceUrl + '/' + action + '/quick-edit', {email_templates: selected}).then(function (response) {
                        if (response.data && response.data.success) {
                            progress.finish();
                            vm.quickEdit = '';
                            swal({
                                title: "Success",
                                text: response.data.success,
                                type: 'success',
                                animation: 'slide-from-bottom'
                            }, function () {
                                vm.fetchTemplates();
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
            exportTemplate(id) {
                window.open(this.resourceUrl + '/export?id=' + id);
            },
            changeSort(attr) {
                var orderToggle = ( this.orderAttr == attr ) ? this.orderToggle * -1 : 1;
                this.fetchTemplates(attr, orderToggle);
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
                    per_page: ( userEmailTemplatesSettings.paginate && userEmailTemplatesSettings.paginate.length ) ? +userEmailTemplatesSettings.paginate : 25,
                    current_page: 1,
                    last_page: 0,
                    from: 1,
                    to: ( userEmailTemplatesSettings.paginate && userEmailTemplatesSettings.paginate.length ) ? +userEmailTemplatesSettings.paginate : 25
                };
            },
            doSearch() {
                if ( this.search.length ) {
                    this.emailTemplates = null;
                    this.pagination = this.getInitialPagination();
                    this.fetchTemplates();
                }
            },
            cancelSearch() {
                this.search = '';
                this.fetchTemplates();
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
                        title: _.capitalize(action) + " " + vm.selected.length + " templates?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonText: _.capitalize(action),
                    }, function(confirmed) {
                        if ( confirmed )
                            vm.quickEditTemplates();
                        else
                            vm.quickEdit = '';
                    });
                }
            },
            '$route'(to) {
                this.fetchTemplates();
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
