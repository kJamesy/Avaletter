<style>
    .ct-label {
        font-size: 14px;
        font-weight: bold;
    }
</style>
<template>
    <div class="emails-stats" v-cloak="">
        <vue-progress-bar></vue-progress-bar>

        <i v-if="! drawChart" class="fa fa-circle-o-notch fa-spin fa-4x fa-fw"></i>

        <div v-if="drawChart">
            <h3>Stats for Email: {{ email.subject }}</h3>
            <div style="float: left; margin: 30px 0;">
                <label for="viewStats">Select Stats to View</label>
                <select v-model="viewStats" id="viewStats" >
                    <option v-for="option in statsOptions" v-bind:value="option.value">
                        {{ option.text }}
                    </option>
                </select>
            </div>
            <div style="float: right; margin: 30px 0;">
                <router-link v-bind:to="{ name: 'emails.index' }" title="Sent Emails" class="btn btn-default btn-xs" exact><i class="fa fa-home"></i></router-link>
                &nbsp; &nbsp;
                <router-link v-bind:to="{ name: 'emails.view', params: { id: email.id } }" title="View" class="btn btn-default btn-xs" exact><i class="fa fa-envelope-open"></i></router-link>
                &nbsp; &nbsp;
                <a class="btn btn-default btn-xs" v-on:click.prevent="refreshStats" href="" title="Refresh" exact><i class="fa fa-refresh"></i></a>
            </div>
            <div style="clear: both;"> </div>
            <chartist type="Bar" v-if="viewStats == ''" :data="chartBarData" :options="chartBarOptions" :event-handlers="chartEvents" ratio="ct-major-second"></chartist>
            <chartist type="Pie" v-if="viewStats.length" :data="chartPieData" :options="chartPieOptions" :event-handlers="chartEvents" ratio="ct-major-second"></chartist>
            <div style="clear: both; margin: 100px 0;"></div>
        </div>
    </div>
</template>
<script>
    export default {
        mounted() {
            this.$nextTick(function() {
                this.resourceUrl = emailsLinks.baseUri;
                this.getEmailWithStats();
            });
        },
        data() {
            return {
                id: this.$route.params.id,
                drawChart: false,
                email: null,
                chartBarData: {
                    labels: [],
                    series: []
                },
                chartBarOptions: {
                    distributeSeries: true,
                    plugins: [
                        this.$chartist.plugins.tooltip({
                            anchorToPoint: false
                        })
                    ],
                    axisX: {
                        position: 'end'
                    },
                    axisY: {
                        onlyInteger: true,
                        labelInterpolationFnc: function(value) {
                            return value;
                        }
                    },
                },
                chartEvents: [{
                    event: 'draw', fn(data) {
                        if ( data.type === 'bar' ) {
                            data.element.attr({
                                style: 'stroke-width: 10px'
                            });
                            data.element.animate({
                                opacity: {
                                    dur: 3000,
                                    from: 0,
                                    to: 1,
                                    easing: 'easeOutQuint'
                                },
                                y2: {
                                    id: 'grow',
                                    dur: 2000,
                                    from: data.y1,
                                    to: data.y2,
                                    easing: 'easeOutQuint'
                                },
                                'stroke-width': [
                                    {
                                        begin: 'grow.endEvent',
                                        dur: 1000,
                                        from: 10,
                                        to: 25,
                                        fill: 'freeze',
                                        easing: 'easeOutQuint'
                                    }
                                ]
                            }, false);
                        }
                        if ( data.type === 'slice' ) { //See https://gionkunz.github.io/chartist-js/examples.html#example-donut-animation
                            var pathLength = data.element._node.getTotalLength();

                            data.element.attr({
                                'stroke-dasharray': pathLength + 'px ' + pathLength + 'px'
                            });

                            var animationDefinition = {
                                'stroke-dashoffset': {
                                    id: 'anim' + data.index,
                                    dur: 1000,
                                    from: -pathLength + 'px',
                                    to:  '0px',
                                    easing: 'easeOutQuint',
                                    fill: 'freeze'
                                }
                            };

                            if( data.index !== 0 )
                                animationDefinition['stroke-dashoffset'].begin = 'anim' + (data.index - 1) + '.end';

                            data.element.attr({
                                'stroke-dashoffset': -pathLength + 'px'
                            });

                            data.element.animate(animationDefinition, false);
                        }
                    }
                }],
                progressPauseAt: null,
                progressPercentage: 0,
                statsOptions: [
                    { text: 'General Stats', value: '' },
                    { text: 'Deliveries', value: 'deliveries' },
                    { text: 'Reads', value: 'opens' },
                    { text: 'Clicks', value: 'clicks' },
                    { text: 'Delivery Countries', value: 'd_countries' },
                    { text: 'Delivery Browsers', value: 'd_browsers' },
                    { text: 'Delivery Devices', value: 'd_devices' }
                ],
                viewStats: '',
                fetchedStats: [],
                chartPieData: {
                    labels: [],
                    series: []
                },
                chartPieDeliveriesData: {
                    labels: [],
                    series: []
                },
                chartPieOpensData: {
                    labels: [],
                    series: []
                },
                chartPieClicksData: {
                    labels: [],
                    series: []
                },
                chartPieDCountriesData: {
                    labels: [],
                    series: []
                },
                chartPieDBrowsersData: {
                    labels: [],
                    series: []
                },
                chartPieDDevicesData: {
                    labels: [],
                    series: []
                },
                chartPieOptions: {
                    donut: true,
                    donutWidth: 80,
                    plugins: [
                        this.$chartist.plugins.tooltip({
                            anchorToPoint: false
                        })
                    ],
                }
            }
        },
        methods: {
            getEmailWithStats() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();
                vm.progressPauseAt = moment().add(5, 'seconds');
                vm.progressTimer();
                vm.fireSpecialWatchers();

                vm.$http.post(vm.resourceUrl + '/' + vm.id + '/sent-email/general-stats').then(function(response) {
                    if (response.data) {
                        var email = response.data.email;

                        if ( email && email.email_injections_count ) {
                            var injectionCount = email.email_injections_count;
                            var deliveryCount = email.email_deliveries_count;
                            var openCount = email.email_opens_count;
                            var clickedCount = email.email_clicks_count;

                            var deliveriesCentage = _.round(_.divide(deliveryCount, injectionCount) * 100, 1);
                            var readsCentage = _.round(_.divide(openCount, injectionCount) * 100, 1);
                            var clickedCentage = _.round(_.divide(clickedCount, injectionCount) * 100, 1);

                            vm.chartBarData = {
                                labels: ['Injections', 'Deliveries', 'Reads', 'Clicked'],
                                series: [
                                    { meta: 'Injections', value: injectionCount },
                                    { meta: 'Deliveries (' + deliveriesCentage + '%)', value: deliveryCount },
                                    { meta: 'Reads (' + readsCentage + '%)', value: openCount },
                                    { meta: 'Clicked (' + clickedCentage + '%)', value: clickedCount }
                                ]
                            };

                            vm.email = email;
                            vm.drawChart = true;
                            progress.finish();
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
            },
            getSelectedStats() {
                var vm = this;
                var progress = vm.$Progress;
                progress.start();
                vm.progressPauseAt = moment().add(5, 'seconds');
                vm.progressTimer();
                vm.fireSpecialWatchers();

                vm.$http.post(vm.resourceUrl + '/' + vm.id + '/sent-email/' + vm.viewStats + '/stats').then(function(response) {
                    if (response.data) {
                        var email = response.data.email;

                        if ( email ) {
                            var injectionCount = email.email_injections_count;

                            if ( injectionCount ) {
                                if (vm.viewStats == 'deliveries') {
                                    var deliveryCount = email.email_deliveries_count;
                                    var undeliveredCount = _.subtract(injectionCount, deliveryCount);

                                    var deliveriesCentage = _.round(_.divide(deliveryCount, injectionCount) * 100, 1);
                                    var undelivereredCentage = _.round(_.divide(undeliveredCount, injectionCount) * 100, 1);

                                    vm.chartPieDeliveriesData = {
                                        labels: ['Delivered', 'Undelivered'],
                                        series: [
                                            {meta: 'Delivered (' + deliveriesCentage + '%)', value: deliveryCount},
                                            {
                                                meta: 'Undelivered (' + undelivereredCentage + '%)',
                                                value: undeliveredCount
                                            },
                                        ]
                                    };

                                    vm.chartPieData = vm.chartPieDeliveriesData;
                                }

                                if (vm.viewStats == 'opens') {
                                    var openCount = email.email_opens_count;
                                    var unopenedCount = _.subtract(injectionCount, openCount);

                                    var opensCentage = _.round(_.divide(openCount, injectionCount) * 100, 1);
                                    var unopenedCentage = _.round(_.divide(unopenedCount, injectionCount) * 100, 1);

                                    vm.chartPieOpensData = {
                                        labels: ['Read', 'Unread'],
                                        series: [
                                            {meta: 'Read (' + opensCentage + '%)', value: openCount},
                                            {meta: 'Unread (' + unopenedCentage + '%)', value: unopenedCount},
                                        ]
                                    };

                                    vm.chartPieData = vm.chartPieOpensData;
                                }

                                if (vm.viewStats == 'clicks') {
                                    var clickCount = email.email_clicks_count;
                                    var unclickedCount = _.subtract(injectionCount, clickCount);

                                    var clicksCentage = _.round(_.divide(clickCount, injectionCount) * 100, 1);
                                    var unclickedCentage = _.round(_.divide(unclickedCount, injectionCount) * 100, 1);

                                    vm.chartPieClicksData = {
                                        labels: ['Clicked', 'Not clicked'],
                                        series: [
                                            {meta: 'Clicked (' + clicksCentage + '%)', value: clickCount},
                                            {meta: 'Not clicked (' + unclickedCentage + '%)', value: unclickedCount},
                                        ]
                                    };

                                    vm.chartPieData = vm.chartPieClicksData;
                                }
                            }

                            if ( vm.viewStats == 'd_countries' ) {
                                var labels = [], series = [];

                                _.forEach(email, function(stat) {
                                    labels.push(stat.country);
                                    series.push({ meta: 'Subscribers', value: stat.country_count });
                                });

                                vm.chartPieDCountriesData = {
                                    labels: labels,
                                    series: series
                                };

                                vm.chartPieData = vm.chartPieDCountriesData;
                            }

                            vm.drawChart = true;
                            if ( _.indexOf(vm.fetchedStats, vm.viewStats) == -1 )
                                vm.fetchedStats.push(vm.viewStats);
                            progress.finish();
                        }
                        else {
                            progress.fail();
                            swal({ title: "An Error Occurred", text: 'No statistics found.', type: 'error', animation: 'slide-from-top'}, function() {
                                vm.$router.replace({ name: 'emails.index' });
                            });
                        }
                    }
                }, function(error) {
                    progress.fail();
                    swal({ title: "An Error Occurred", text: 'No statistics found.', type: 'error', animation: 'slide-from-top'}, function() {
                        vm.$router.replace({ name: 'emails.index' });
                    });
                });
            },
            progressTimer() {
                var vm = this;

                if ( vm.progressPauseAt ) {
                    _.delay(function () {
                        if ( vm.progressPercentage > 80 && ( moment().isSame(vm.progressPauseAt) || moment().isAfter(vm.progressPauseAt) ) ) {
                            vm.$Progress.pause();
                            vm.progressPauseAt = null;
                        }

                        vm.progressTimer();
                    }, 500);
                }
            },
            fireSpecialWatchers() {
                this.$watch(function() {
                            return this.$Progress.get();
                        }, function(newVal) {
                            if ( newVal > 0 && newVal < 100 )
                                this.progressPercentage = newVal;
                            else
                                this.progressPercentage = 0;
                        }
                )
            },
            refreshStats() {
                if ( this.viewStats.length )
                    this.getSelectedStats();
                else
                    this.getEmailWithStats();
            }
        },
        watch: {
            viewStats(newVal) {
                var vm = this;
                if ( newVal.length && _.indexOf(vm.fetchedStats, newVal) == -1 )
                    vm.getSelectedStats();
                else if ( newVal.length ) {
                    switch ( newVal ) {
                        case 'deliveries':
                            vm.chartPieData = vm.chartPieDeliveriesData;
                            break;
                        case 'opens':
                            vm.chartPieData = vm.chartPieOpensData;
                            break;
                        case 'clicks':
                            vm.chartPieData = vm.chartPieClicksData;
                            break;
                        case 'd_countries':
                            vm.chartPieData = vm.chartPieDCountriesData;
                            break;
                        case 'd_browsers':
                            vm.chartPieData = vm.chartPieDBrowsersData;
                            break;
                        case 'd_devices':
                            vm.chartPieData = vm.chartPieDDevicesData;
                            break;
                    }
                }

            }
        }
    }
</script>
