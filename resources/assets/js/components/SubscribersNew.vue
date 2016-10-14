<template>
    <div class="subscribers-new">
        <form v-on:submit.prevent='addSubscriber'>
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
            <button type="submit" class="btn btn-default">Submit</button>
        </form>
    </div>
</template>

<script>
    export default{
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = 'subscribers';
                this.createSubscriber();
            });
        },
        data() {
            return {
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
            createSubscriber() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();

                vm.$http.get(vm.resourceUrl + '/create').then(function(response) {
                    if ( response.data )
                        vm.mLists = response.data;
                    progress.finish();
                }, function(error) {
                    swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    progress.fail();
                });
            },
            addSubscriber() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();
                vm.clearValidationErrors();

                vm.$http.post(vm.resourceUrl, vm.subscriber).then(function(response) {
                    swal({ title: "Success", text: 'Subscriber created', type: 'success', animation: 'slide-from-bottom', timer: 3000 });
                    vm.clearSubscriber();
                    progress.finish();
                }, function(error) {
                    if ( error.status && error.status == 422 && error.data ) {
                        swal({title: "An Error Occurred", text: 'Please check the highlighted fields and try again', type: 'error', animation: 'slide-from-top', timer: 3000});

                        _.forEach(error.data, function(message, field) {
                           vm.$set(vm.validation, field, message[0]);
                        });
                    }
                    else {
                        swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    }
                    progress.fail();
                });
            },
            clearValidationErrors() {
                this.$set(this, 'validation', {first_name: '', last_name: '', email: ''});
            },
            clearSubscriber() {
                this.$set(this, 'subscriber', {first_name: '', last_name: '', email: '', active: 1, mailing_lists: []});
            }
        },
        filters: {
            capitalize(string) {
                return _.capitalize(string);
            }
        }
    }
</script>
