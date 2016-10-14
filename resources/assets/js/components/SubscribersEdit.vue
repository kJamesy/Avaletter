<template>
    <div class="subscribers-edit">
        <form v-on:submit.prevent='updateSubscriber'>
            <div class="form-group" v-bind:class="validation.first_name ? 'has-error' : ''">
                <label for="first_name">First Name <span v-if="validation.first_name" class="text-danger">{{ validation.first_name }}</span></label>
                <input type="text" class="form-control" id="first_name" placeholder="First Name" v-model.trim="subscriber.first_name">
            </div>
            <div class="form-group" v-bind:class="validation.last_name ? 'has-error' : ''">
                <label for="last_name">Last Name <span v-if="validation.last_name" class="text-danger">{{ validation.last_name }}</span></label>
                <input type="text" class="form-control" id="last_name" placeholder="Last Name" v-model.trim="subscriber.last_name">
            </div>
            <div class="form-group" v-bind:class="validation.email ? 'has-error' : ''">
                <label for="email">Email <span v-if="validation.email" class="text-danger">{{ validation.email }}</span> </label>
                <input type="text" class="form-control" id="email" placeholder="Email" v-model.trim="subscriber.email">
            </div>
            <div class="form-group checkbox">
                <label>
                    <input type="checkbox" v-model="subscriber.active"> Active
                </label>
            </div>
            <div class="form-group" v-if="mLists">
                <label for="mailing_lists">Mailing Lists</label>
                <select class="form-control" id="mailing_lists" v-model="subscriber.mailing_lists" multiple>
                    <option v-for="mList in sortedMLists" v-bind:value="mList.id">{{ mList.name }}</option>
                </select>
            </div>
            <div class="clearfix">
                <button type="submit" class="btn btn-default">Update</button>
                <router-link v-bind:to="{ name: 'subscribers.index' }" class="btn btn-default" style="float:right" exact replace>Cancel</router-link>
            </div>
        </form>
        <div style="margin-top: 50px">
            <button class="btn-danger btn btn-xs" v-on:click.prevent="deleteSubscriber" style="float:right" title="Delete"><i class="fa fa-times-circle-o"></i></button>
        </div>
    </div>
</template>

<script>
    export default{
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = 'subscribers';
                this.getSubscriber();
            });
        },
        data() {
            return {
                id: this.$route.params.id,
                subscriber: {first_name: '', last_name: '', email: '', active: 1, mailing_lists: []},
                validation: {first_name: '', last_name: '', email: ''},
                mLists: []
            }
        },
        computed: {
            sortedMLists() {
                return _.sortBy(this.mLists, ['name']);
            }
        },
        methods: {
            getSubscriber() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();

                vm.$http.get(vm.resourceUrl + '/' + this.id).then(function(response) {
                    if (response.data) {
                        if ( response.data.subscriber ) {
                            vm.$set(vm.subscriber, 'first_name', response.data.subscriber.first_name);
                            vm.$set(vm.subscriber, 'last_name', response.data.subscriber.last_name);
                            vm.$set(vm.subscriber, 'email', response.data.subscriber.email);
                            vm.$set(vm.subscriber, 'active', response.data.subscriber.active);

                            if ( response.data.subscriber.mailing_lists ) {
                                _.forEach(response.data.subscriber.mailing_lists, function(list) {
                                    vm.subscriber.mailing_lists.push(list.id);
                                });
                            }

                            if (response.data.mLists)
                                vm.mLists = response.data.mLists;
                            progress.finish();
                        }
                        else {
                            progress.fail();
                            swal({ title: "An Error Occurred", text: 'The subscriber does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                                vm.$router.replace({ name: 'subscribers.index' });
                            });
                        }
                    }
                }, function(error) {
                    progress.fail();
                    swal({ title: "An Error Occurred", text: 'The subscriber does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                        vm.$router.replace({ name: 'subscribers.index' });
                    });
                });
            },
            updateSubscriber() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();
                vm.clearValidationErrors();

                vm.$http.put(vm.resourceUrl + '/' + vm.id, vm.subscriber).then(function(response) {
                    swal({ title: "Success", text: 'Subscriber updated', type: 'success', animation: 'slide-from-bottom', timer: 3000 });
                    progress.finish();
                }, function(error) {
                    if ( error.status && error.status == 422 && error.data ) {
                        swal({title: "An Error Occurred", text: 'Please check the highlighted fields and try again', type: 'error', animation: 'slide-from-top', timer: 3000});

                        _.forEach(error.data, function(message, field) {
                           vm.$set(vm.validation, field, message[0]);
                        });
                    }
                    else if ( error.status && error.status == 404 ) {
                        swal({ title: "An Error Occurred", text: 'The subscriber does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                            vm.$router.replace({ name: 'subscribers.index' });
                        });
                    }
                    else {
                        swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    }
                    progress.fail();
                });
            },
            deleteSubscriber() {
                var vm = this;
                var progress = vm.$Progress;
                var subscriber = vm.subscriber;

                swal({
                    title: "Delete subscriber: " + subscriber.first_name + ' ' + subscriber.last_name + "?",
                    text: "The subscriber will be moved to trash",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Delete",
                    closeOnConfirm: false
                }, function() {
                    progress.start();

                    vm.$http.delete(vm.resourceUrl + '/' + subscriber.id ).then(function(response) {
                        if ( response.data && response.data.success ) {
                            progress.finish();
                            vm.clearSubscriber();
                            swal({ title: "Success", text: response.data.success, type: 'success', animation: 'slide-from-bottom'}, function() {
                                vm.$router.replace({ name: 'subscribers.index' });
                            });
                        }
                    }, function(error) {
                        progress.fail();

                        if ( error.status && error.status == 404 ) {
                            swal({ title: "An Error Occurred", text: 'The subscriber does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                                vm.$router.replace({ name: 'subscribers.index' });
                            });
                        }
                        else
                            swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    });
                });
            },
            clearValidationErrors() {
                this.$set(this, 'validation', {first_name: '', last_name: '', email: ''});
            },
            clearSubscriber() {
                this.$set(this, 'subscriber', {first_name: '', last_name: '', email: '', mailing_lists: []});
            }

        },
        filters: {
            capitalize(string) {
                return _.capitalize(string);
            }
        }
    }
</script>
