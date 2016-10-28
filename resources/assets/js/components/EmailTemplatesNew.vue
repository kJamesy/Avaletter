<template>
    <div class="subscribers-new">
        <div class="clearfix">
            <h3>Add Email Template</h3>
        </div>
        <form v-on:submit.prevent='addTemplate'>
            <div class="form-group" v-bind:class="validation.name ? 'has-error' : ''">
                <label for="name">Name <span v-if="validation.name" class="text-danger">{{ validation.name }}</span></label>
                <input type="text" class="form-control" id="name" placeholder="Name" v-model.trim="emailTemplate.name">
            </div>
            <div class="form-group" v-bind:class="validation.content ? 'has-error' : ''">
                <label for="content">Content <span v-if="validation.content" class="text-danger">{{ validation.content }}</span> </label>
                <textarea id="content" class="form-control tinymce hidden"></textarea>
            </div>
            <div class="clearfix">
                <button type="submit" class="btn btn-default">Submit</button>
                <router-link v-bind:to="{ name: 'emailTemplates.index' }" class="btn btn-default" style="float:right" exact replace>Cancel</router-link>
            </div>
        </form>
    </div>
</template>

<script>
    export default{
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = emailTemplatesLinks.baseUri;
                this.initTinyMce();
            });
        },
        data() {
            return {
                emailTemplate: {name: '', content: ''},
                validation: {name: '', content: ''}
            }
        },
        methods: {
            addTemplate() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();
                vm.clearValidationErrors();

                vm.$http.post(vm.resourceUrl, vm.emailTemplate).then(function(response) {
                    swal({ title: "Success", text: 'Email template created', type: 'success', animation: 'slide-from-bottom', timer: 3000 });
                    vm.clearTemplate();
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
