<template>
    <div class="subscribers-edit">
        <div class="clearfix">
            <h3>Edit Email Template</h3>
        </div>
        <form v-on:submit.prevent='updateTemplate'>
            <div class="form-group" v-bind:class="validation.name ? 'has-error' : ''">
                <label for="name">Name <span v-if="validation.name" class="text-danger">{{ validation.name }}</span></label>
                <input type="text" class="form-control" id="name" placeholder="Name" v-model.trim="emailTemplate.name">
            </div>
            <div class="form-group" v-bind:class="validation.content ? 'has-error' : ''">
                <label for="content">Email <span v-if="validation.content" class="text-danger">{{ validation.content }}</span> </label>
                <textarea id="content" class="form-control tinymce hidden"></textarea>
            </div>
            <div class="clearfix">
                <button type="submit" class="btn btn-default">Update</button>
                <router-link v-bind:to="{ name: 'emailTemplates.index' }" class="btn btn-default" style="float:right" exact replace>Cancel</router-link>
            </div>
        </form>
        <div style="margin-top: 50px">
            <button class="btn-danger btn btn-xs" v-on:click.prevent="deleteTemplate" style="float:right" title="Delete"><i class="fa fa-times-circle-o"></i></button>
        </div>
    </div>
</template>

<script>
    export default{
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = emailTemplatesLinks.baseUri;
                this.getTemplate();
            });
        },
        data() {
            return {
                id: this.$route.params.id,
                emailTemplate: {name: '', content: ''},
                validation: {name: '', content: ''}
            }
        },
        methods: {
            getTemplate() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();

                vm.$http.get(vm.resourceUrl + '/' + this.id).then(function(response) {
                    if (response.data) {
                        if ( response.data.email_template ) {
                            vm.emailTemplate.name = response.data.email_template.name;
                            vm.emailTemplate.content = response.data.email_template.content;

                            this.initTinyMce();
                            progress.finish();
                        }
                        else {
                            progress.fail();
                            swal({ title: "An Error Occurred", text: 'The email template does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                                vm.$router.replace({ name: 'emailTemplates.index' });
                            });
                        }
                    }
                }, function(error) {
                    progress.fail();
                    swal({ title: "An Error Occurred", text: 'The email template does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                        vm.$router.replace({ name: 'emailTemplates.index' });
                    });
                });
            },
            updateTemplate() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();
                vm.clearValidationErrors();

                vm.$http.put(vm.resourceUrl + '/' + vm.id, vm.emailTemplate).then(function(response) {
                    swal({ title: "Success", text: 'Email template updated', type: 'success', animation: 'slide-from-bottom', timer: 3000 });
                    progress.finish();
                }, function(error) {
                    if ( error.status && error.status == 422 && error.data ) {
                        swal({title: "An Error Occurred", text: 'Please check the highlighted fields and try again', type: 'error', animation: 'slide-from-top', timer: 3000});

                        _.forEach(error.data, function(message, field) {
                           vm.$set(vm.validation, field, message[0]);
                        });
                    }
                    else if ( error.status && error.status == 404 ) {
                        swal({ title: "An Error Occurred", text: 'The email template does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                            vm.$router.replace({ name: 'emailTemplates.index' });
                        });
                    }
                    else
                        swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    progress.fail();
                });
            },
            deleteTemplate() {
                var vm = this;
                var progress = vm.$Progress;
                var template = vm.emailTemplate;

                swal({
                    title: "Delete email template: " + template.name + "?",
                    text: "The email template will be permanently deleted",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Delete",
                    closeOnConfirm: false
                }, function() {
                    progress.start();

                    vm.$http.delete(vm.resourceUrl + '/' + vm.id ).then(function(response) {
                        if ( response.data && response.data.success ) {
                            progress.finish();
                            vm.clearTemplate();
                            swal({ title: "Success", text: response.data.success, type: 'success', animation: 'slide-from-bottom'}, function() {
                                vm.$router.replace({ name: 'emailTemplates.index' });
                            });
                        }
                    }, function(error) {
                        progress.fail();

                        if ( error.status && error.status == 404 ) {
                            swal({ title: "An Error Occurred", text: 'The email template does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                                vm.$router.replace({ name: 'emailTemplates.index' });
                            });
                        }
                        else
                            swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                    });
                });
            },
            clearValidationErrors() {
                this.$set(this, 'validation', {name: '', content: ''});
            },
            clearTemplate() {
                this.$set(this, 'emailTemplate', {name: '', content: ''});
            },
            initTinyMce() {
                var vm = this;

                _.delay(function() {
                    tinymce.remove();
                    var newCOnfig = {
                        setup: function(editor) {
                            editor.on('init', function() {
                                editor.setContent(vm.emailTemplate.content);
                            });

                            editor.on('change keyup', function() {
                                vm.emailTemplate.content = editor.getContent();
                            });
                        }
                    };

                    tinymce.init(_.assign(tinyMCEConfig, newCOnfig));
                }, 300);
            }
        }
    }
</script>
