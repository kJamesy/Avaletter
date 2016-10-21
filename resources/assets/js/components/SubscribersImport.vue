<template>
    <div class="subscribers-import">
        <div class="clearfix">
            <h3>Batch Import Subscribers</h3>
        </div>
        <form id="upload" v-if="! givingFeedback">
            <div class="form-group" v-bind:class="validation.file ? 'has-error' : ''">
                <label for="file">Excel File <span v-if="validation.file" class="text-danger">{{ validation.file }}</span> </label>
                <input type="file" id="file" v-on:change="fileInserted($event)" name="upload">
                <div class="info">
                    <span v-if="subscribers.file.type.length">Type: {{ subscribers.file.type }} | </span>
                    <span v-if="subscribers.file.size">Size: {{ subscribers.file.size | convertToKb }} Kb</span>
                </div>
            </div>
            <div class="form-group checkbox">
                <label>
                    <input type="checkbox" v-model="subscribers.active" name="active"> Active
                </label>
            </div>
            <div class="form-group" v-if="mLists" v-bind:class="validation.mailing_lists ? 'has-error' : ''">
                <label for="mailing_lists">Mailing Lists <span v-if="validation.mailing_lists" class="text-danger">{{ validation.mailing_lists }}</span> </label>
                <select class="form-control" id="mailing_lists" v-model="subscribers.mailing_lists" multiple>
                    <option v-for="mList in sortedMLists" v-bind:value="mList.id">{{ mList.name }}</option>
                </select>
            </div>
            <div class="clearfix">
                <button type="submit" class="btn btn-default" v-on:click.prevent='importSubscribers' v-if="proceedWithUpload">Next Step</button>
                <router-link v-bind:to="{ name: 'subscribers.index' }" class="btn btn-default" style="float:right" exact replace>Cancel</router-link>
            </div>
        </form>
        <div v-if="givingFeedback">
            <p>There were {{ rowsCount }} rows in the uploaded file.</p>
            <div v-if="badRows.length">
                <p>Rows with errors: {{ badRows.length }} </p>
                <p>Please check the following row numbers for missing or bad names/email:<br /> {{ badRows | arrToString }} </p>
            </div>
            <div class="form-group" v-bind:class="nextStepValidation ? 'has-error' : ''">
                <label for="finish_import">Select Next Step <span v-if="nextStepValidation" class="text-danger">{{ nextStepValidation }}</span></label>
                <select class="form-control" id="finish_import" v-model="finalStep">
                    <option value="">Next Step</option>
                    <option value="proceed" v-if="rowsCount - badRows.length">Save Good Rows ({{ rowsCount - badRows.length }})</option>
                    <option value="cancel">Cancel Import</option>
                </select>
            </div>
            <button v-on:click="finaliseImport" class="btn btn-default">Finish</button>
        </div>
    </div>
</template>

<script>
    export default{
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = subscribersLinks.baseUri;
                this.createSubscriber();
            });
        },
        data() {
            return {
                subscribers: {file: {name: '', size: 0, type: ''}, active: 1, mailing_lists: []},
                validation: {file: '', mailing_lists: ''},
                mLists: [],
                proceedWithUpload: false,
                allowedExtensions: ['xls', 'xlt', 'csv'],
                givingFeedback: false,
                fileName: null,
                rowsCount: 0,
                badRows: [],
                finalStep: '',
                nextStepValidation: ''
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
                if( typeof FormData === 'undefined' ) {
                    swal({ title: "Browser says no!", text: 'Your browser is unfortunately too old for this next part. Please use a modern browser.', type: 'error', animation: 'slide-from-top'}, function() {
                        vm.$router.push({
                            name: 'subscribers.new'
                        });
                    });
                }
                else {
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
                }
            },
            importSubscribers() {
                var vm = this;

                if ( vm.mLists && vm.subscribers.mailing_lists.length ) {
                    var progress = vm.$Progress;
                    var formData = new FormData();

                    formData.append('upload', $("input[name='upload']")[0].files[0]);
                    formData.append('isImporting', 1);

                    progress.start();
                    vm.clearValidationErrors();

                    vm.$http.post(vm.resourceUrl, formData).then(function(response) {
                        if ( response.data.success && response.data.fileName ) {
                            vm.fileName = response.data.fileName;
                            vm.rowsCount = response.data.rowsCount;
                            vm.badRows = response.data.badRows;
                            vm.givingFeedback = true;
                        }
                        progress.finish();
                    }, function(error) {
                        if ( error.status && error.status == 422 && error.data ) {
                            swal({title: "An Error Occurred", text: 'Please check the highlighted fields and try again', type: 'error', animation: 'slide-from-top', timer: 3000});

                            _.forEach(error.data, function(message, field) {
                               vm.$set(vm.validation, field, message[0]);
                            });
                        }
                        else if ( error && error.status == 500 && error.data.file_does_not_exist )
                            swal('A server error occurred', error.data.file_does_not_exist, 'error');
                        else
                            swal('An Error Occurred', 'Please refresh the page and try again.', 'error');
                        progress.fail();
                    });
                }
                else {
                    vm.validation.mailing_lists = 'Please select at least one mailing list to import into';
                }

            },
            finaliseImport() {
                var vm = this;
                if ( ! vm.finalStep.length )
                    vm.nextStepValidation = "Please first select how you want to proceed";
                else {
                    vm.nextStepValidation = '';
                    var data = {
                        action: vm.finalStep,
                        fileName: vm.fileName,
                        active: vm.subscribers.active,
                        mailing_lists: vm.subscribers.mailing_lists
                    };

                    var progress = vm.$Progress;
                    progress.start();

                    vm.$http.post(vm.resourceUrl + '/finalise-import', data).then(function(response) {
                        progress.finish();
                        if ( response.data.success ) {
                            var message = response.data.succeededNum + " subscribers were successfully saved in the database.";

                            if ( response.data.failedNum ) {
                                message =+ " " + response.data.failedNum +
                                " could not be stored due to validation issues (likely they already exist in the database - in which case they have been attached to the provided mailing lists";
                            }

                            swal({ title: "Success", text: message, type: 'success', animation: 'slide-from-bottom'}, function() {
                                vm.$router.push({
                                    name: 'subscribers.index',
                                });
                            });
                        }
                        else if ( response.data.cancellation ) {
                            swal({ title: "Success", text: response.data.cancellation, type: 'success', animation: 'slide-from-bottom'}, function() {
                                vm.$router.push({
                                    name: 'subscribers.index',
                                });
                            });
                        }
                    }, function(error) {
                        progress.fail();
                        var message = ( error.status && error.status == 422 && error.data ) ? error.data.no_good_records : 'Please refresh the page and try again.';

                        swal({ title: "An Error Occurred", text: message, type: 'error', animation: 'slide-from-top'}, function() {
                            vm.$router.push({
                                name: 'subscribers.import',
                            });
                        });
                    });

                }
            },
            fileInserted(event) {
                var upload = event.target.files[0];

                if( upload.name && this.hasAllowedExtension(upload.name) ) {
                    var properties = { name: upload.name, size: upload.size, type: upload.type };
                    this.$set(this.subscribers, 'file', properties);
                    this.proceedWithUpload = true;
                    this.clearValidationErrors();
                }
                else
                    this.validation.file = 'The selected file is not allowed';
            },
            hasAllowedExtension(filename) {
                var pass = false;
                var loop = true;

                _.forEach(this.allowedExtensions, function(ext) {
                    var theExt = '.' + _.toLower(ext);

                    if ( loop ) {
                        if ( _.endsWith(_.toLower(filename), theExt) ) {
                            pass = true;
                            loop = false;
                        }
                    }
                });

                return pass;
            },
            clearValidationErrors() {
                this.$set(this, 'validation', {file: '', mailing_lists: ''});
            },
            clearSubscribers() {
                this.$set(this, 'subscribers', {file: {name: '', size: 0, type: ''}, active: 1, mailing_lists: []});
                this.proceedWithUpload = false;
            }
        },
        watch: {
            'validation.file'(newValue) {
                this.proceedWithUpload = ! newValue.length;
            }
        },
        filters: {
            capitalize(string) {
                return _.capitalize(string);
            },
            convertToKb(bytes) {
                return _.round(bytes/1024, 2);
            },
            arrToString(arr) {
                return _.join(arr, ', ');
            }
        }
    }
</script>
