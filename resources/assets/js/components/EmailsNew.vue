<template>
    <div class="subscribers-new">
        <div class="clearfix">
            <h3>New Email</h3>
        </div>
        <form v-on:submit.prevent='addEmail'>
            <div class="form-group" v-if="mLists.length" v-bind:class="validation.mailing_lists ? 'has-error' : ''">
                <label for="mailing_lists">Recipient Mailing Lists <span v-if="validation.mailing_lists" class="text-danger">{{ validation.mailing_lists }}</span></label>
                <select class="form-control" id="mailing_lists" v-model="email.mailing_lists" multiple>
                    <option v-for="mList in sortedMLists" v-bind:value="mList.id">{{ mList.name }}</option>
                </select>
            </div>
            <div class="form-group" v-if="subscribers.length" v-bind:class="validation.subscribers ? 'has-error' : ''">
                <label for="subscribers">Recipient Subscribers <span v-if="validation.subscribers" class="text-danger">{{ validation.subscribers }}</span></label>
                <select class="form-control" id="subscribers" v-model="email.subscribers" multiple>
                    <option v-for="subscriber in sortedSubscribers" v-bind:value="subscriber.id">{{ getSubscriberLabel(subscriber) }}</option>
                </select>
            </div>
            <div class="form-group" v-bind:class="validation.email_edition_id ? 'has-error' : ''">
                <label for="email_edition">Email Edition <span v-if="validation.email_edition_id" class="text-danger">{{ validation.email_edition_id }}</span></label>
                <select class="form-control" id="email_edition" v-model.number="email.email_edition_id">
                    <option value="">Select Email Edition</option>
                    <option v-for="edition in sortedEmailEditions" v-bind:value="edition.id">{{ edition.edition }}</option>
                </select>
            </div>
            <div class="form-group" v-bind:class="validation.subject ? 'has-error' : ''">
                <label for="subject">Subject <span v-if="validation.subject" class="text-danger">{{ validation.subject }}</span></label>
                <input type="text" class="form-control" id="subject" placeholder="Subject" v-model.trim="email.subject">
            </div>
            <div class="form-group" v-bind:class="validation.body ? 'has-error' : ''">
                <label for="content">Email Body <span v-if="validation.body" class="text-danger">{{ validation.body }}</span> </label>
                <textarea id="content" class="form-control tinymce hidden"></textarea>
            </div>
            <div class="form-group checkbox" style="margin: 20px 0;">
                <label>
                    <input type="checkbox" v-model="email.is_draft" v-bind:disabled="disableCheckbox"> Save as Draft
                </label>
            </div>
            <div class="clearfix">
                <button type="submit" class="btn btn-default">{{ buttonText }}</button>
                <router-link v-bind:to="{ name: 'emails.index' }" class="btn btn-default" style="float:right" exact replace>Cancel</router-link>
            </div>
        </form>
    </div>
</template>

<script>
    export default{
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = emailsLinks.baseUri;
                this.initTinyMce();
                this.createEmail();
                this.fireSpecialWatchers();
            });
        },
        data() {
            return {
                email: { mailing_lists: [], subscribers: [], email_edition_id: '', subject: '', body: '', is_draft: true },
                validation: { mailing_lists: '', subscribers: '', email_edition_id: '', subject: '', body: '' },
                mLists: [],
                subscribers: [],
                emailEditions: [],
                disableCheckbox: true,
                buttonText: 'Save Draft',
            }
        },
        computed: {
            sortedMLists() {
                return _.sortBy(this.mLists, ['name']);
            },
            sortedSubscribers() {
                return _.sortBy(this.subscribers, ['first_name', 'last_name']);
            },
            sortedEmailEditions() {
                return _.sortBy(this.emailEditions, ['edition']);
            }
        },
        methods: {
            createEmail() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();

                vm.$http.get(vm.resourceUrl + '/create').then(function(response) {
                    if ( response.data ) {
                        this.mLists = response.data.mailing_lists ? response.data.mailing_lists : [];
                        this.subscribers = response.data.subscribers ? response.data.subscribers : [];
                        this.emailEditions = response.data.email_editions ? response.data.email_editions : [];
                    }
                    progress.finish();
                }, function(error) {
                    swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    progress.fail();
                });
            },
            addEmail() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();
                vm.clearValidationErrors();

                vm.$http.post(vm.resourceUrl, vm.email).then(function(response) {
                    swal({ title: "Success", text: 'Email created', type: 'success', animation: 'slide-from-bottom', timer: 3000 });
                    vm.clearEmail();
                    vm.initTinyMce();

                    progress.finish();
                }, function(error) {
                    if ( error.status && error.status == 422 && error.data ) {
                        swal({title: "An Error Occurred", text: 'Please check the highlighted fields and try again', type: 'error', animation: 'slide-from-top', timer: 3000});

                        _.forEach(error.data, function(message, field) {
                           vm.$set(vm.validation, field, message[0]);
                        });
                    }
                    else
                        swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    progress.fail();
                });
            },
            clearValidationErrors() {
                this.$set(this, 'validation', { mailing_lists: '', subscribers: '', email_edition_id: '', subject: '', body: '' });
            },
            clearEmail() {
                this.$set(this, 'email', { mailing_lists: [], subscribers: [], email_edition_id: '', subject: '', body: '', is_draft: true });
            },
            initTinyMce() {
                var vm = this;

                _.delay(function() {
                    tinymce.remove();
                    var newCOnfig = {
                        setup: function(editor) {
                            editor.on('init', function() {
                                editor.setContent(vm.email.body);
                            });

                            editor.on('change keyup', function() {
                                vm.email.body = editor.getContent();
                            });
                        }
                    };

                    tinymce.init(_.assign(tinyMCEConfig, newCOnfig));
                }, 300);
            },
            getSubscriberLabel(subscriber) {
                return subscriber.first_name + ' ' + subscriber.last_name + ' <' + subscriber.email + '>';
            },
            fireSpecialWatchers() {
                this.$watch(function() {
                            return this.email.subscribers.length + this.email.mailing_lists.length;
                        }, function(newVal) {
                            this.disableCheckbox = newVal ? false : true;
                            this.buttonText = ( newVal && ! this.email.is_draft ) ? 'Send Now' : 'Save Draft';
                        }
                )
            }
        },
        watch: {
            'email.is_draft'(newVal) {
                this.buttonText = newVal ? 'Save Draft' : 'Send Now';
            },
            'buttonText'(newVal) {
                this.email.is_draft = (newVal == 'Save Draft' );
            }
        }

    }
</script>
