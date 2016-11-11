<template>
    <div class="emails-stats" v-cloak>
        <vue-progress-bar></vue-progress-bar>

        <i v-if="! drawChart" class="fa fa-circle-o-notch fa-spin fa-4x fa-fw"></i>
        <chartist v-if="drawChart" type="Bar" :data="chartData" :options="chartOptions" :event-handlers="chartEvents" ratio="ct-major-second"></chartist>
    </div>
</template>
<script>
    export default {
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = emailsLinks.baseUri;
                this.getEmail();
            });
        },
        data() {
            return {
                id: this.$route.params.id,
                drawChart: false,
                chartData: {
                    labels: ['Injections', 'Deliveries', 'Reads'],
                    series: [
                        [
                            { meta: 'Injections', value: 0 },
                            { meta: 'Deliveries', value: 0 },
                            { meta: 'Reads', value: 0 }
                        ]
                    ]
                },
                chartOptions: {
                    plugins: [
                        this.$chartist.plugins.tooltip()
                    ]
//                    distributeSeries: true
                },
                chartEvents: [{
                    event: 'draw', fn(data) {
                        if(data.type === 'bar') {
                            data.element.attr({
                                style: 'stroke-width: 50px'
                            });
                            data.element.animate({
                                opacity: {
                                    dur: 1000,
                                    from: 0,
                                    to: 1
                                },
                            });
                        }
                    }
                }]
            }
        },
        methods: {
            getEmail() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();

                vm.$http.get(vm.resourceUrl + '/' + vm.id + '/stats').then(function(response) {
                    if (response.data) {
                        if ( response.data.email ) {
                            var email = response.data.email;
                            var injections = 0;
                            var deliveries = 0;
                            var opens = 0;

                            if ( email.email_injections ) {
                                injections = email.email_injections.length;

                                _.forEach(email.email_injections, function(injection) {
                                   if ( injection.email_delivery )
                                       deliveries++;
                                    if ( injection.email_open )
                                        opens++;
                                });
                            }

                            vm.chartData.series =  [
                                [
                                    {meta: 'Injections', value: injections },
                                    {meta: 'Deliveries', value: deliveries },
                                    {meta: 'Reads', value: opens }
                                ]
                            ];

                            vm.drawChart = true;
                            progress.finish();
//                            vm.successfulFetch = true;
                        }
                        else {
                            progress.fail();
                            swal({ title: "An Error Occurred", text: 'The email does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                                vm.$router.replace({ name: 'emails.index' });
                            });
                        }
                    }
                }, function(error) {
                    progress.fail();
                    swal({ title: "An Error Occurred", text: 'The email does not exist', type: 'error', animation: 'slide-from-top'}, function() {
                        vm.$router.replace({ name: 'emails.index' });
                    });
                });
            }
        }
    }
</script>
