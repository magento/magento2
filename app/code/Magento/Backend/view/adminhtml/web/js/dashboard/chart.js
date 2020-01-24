/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*global define*/
/*global FORM_KEY*/
define([
    'jquery',
    'chartJs',
    'jquery-ui-modules/widget',
    'moment'
], function ($, Chart) {
    'use strict';

    $.widget('mage.dashboardChart', {
        options: {
            updateUrl: '',
            periodSelect: null,
            type: ''
        },
        chart: null,

        /**
         * @private
         */
        _create: function () {
            this.createChart();

            if (this.options.periodSelect) {
                $(document).on('change', this.options.periodSelect, this.refreshChartData.bind(this));

                this.period = $(this.options.periodSelect).val();
            }
        },

        /**
         * @public
         */
        createChart: function () {
            this.chart = new Chart(this.element, this.getChartSettings());
            this.refreshChartData();
        },

        /**
         * @public
         */
        refreshChartData: function () {
            var data = {
                'form_key': FORM_KEY
            };

            if (this.options.periodSelect) {
                this.period = data.period = $(this.options.periodSelect).val();
            }

            $.ajax({
                url: this.options.updateUrl,
                showLoader: true,
                data: data,
                dataType: 'json',
                type: 'POST',
                success: this.updateChart.bind(this)
            });
        },

        /**
         * @public
         * @param {Object} response
         */
        updateChart: function (response) {
            $(this.element).toggle(response.data.length > 0);
            $(this.element).next('.dashboard-diagram-nodata').toggle(response.data.length === 0);

            this.chart.options.scales.xAxes[0].time.unit = this.getPeriodUnit();
            this.chart.data.datasets[0].data = response.data;
            this.chart.data.datasets[0].label = response.label;
            this.chart.update();
        },

        /**
         * @returns {String} time unit per currently set period
         */
        getPeriodUnit: function () {
            switch (this.period) {
                case '7d':
                case '1m':
                    return 'day';

                case '1y':
                case '2y':
                    return 'month';
            }

            return 'hour';
        },

        /**
         * @returns {Number} precision of numeric chart data
         */
        getPrecision: function () {
            if (this.options.type === 'amounts') {
                return 2;
            }

            return 0;
        },

        /**
         * @returns {Object} chart object configuration
         */
        getChartSettings: function () {
            return {
                type: 'bar',
                data: {
                    datasets: [{
                        data: [],
                        backgroundColor: '#f1d4b3',
                        borderColor: '#eb5202',
                        borderWidth: 1
                    }]
                },
                options: {
                    legend: {
                        onClick: this.handleChartLegendClick,
                        position: 'bottom'
                    },
                    scales: {
                        xAxes: [{
                            offset: true,
                            type: 'time',
                            ticks: {
                                autoSkip: true,
                                source: 'data'
                            }
                        }],
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                precision: this.getPrecision()
                            }
                        }]
                    }
                }
            };
        },

        /**
         * @public
         */
        handleChartLegendClick: function () {
            // don't hide dataset on clicking into legend item
        }
    });

    return $.mage.dashboardChart;
});
