/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

/*global FORM_KEY*/
define([
    'jquery',
    'chartJs',
    'jquery-ui-modules/widget',
    'chartjs/chartjs-adapter-moment',
    'chartjs/es6-shim.min',
    'moment'
], function ($, Chart) {
    'use strict';

    $.widget('mage.dashboardChart', {
        options: {
            updateUrl: '',
            responsive: true,
            maintainAspectRatio: false,
            periodSelect: null,
            periodUnits: [],
            precision: 0,
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
            const data = {};

            if (this.options.periodSelect) {
                this.period = data.period = $(this.options.periodSelect).val();
            }

            this._request(data).done(this.updateChart.bind(this));
        },

        /**
         * @param {Object} data
         * @returns {jqXHR}
         * @private
         */
        _request: function (data) {
            return $.ajax({
                url: this.options.updateUrl,
                showLoader: true,
                data: $.extend({form_key: FORM_KEY}, data),
                dataType: 'json',
                type: 'POST'
            });
        },

        /**
         * @public
         * @param {Object} response
         */
        updateChart: function (response) {
            $(this.element).parent()
                .toggle(response.data.length > 0)
                .next('.dashboard-diagram-nodata')
                .toggle(response.data.length === 0);
            this.chart.options.scales.xAxis.time.unit = this.options.periodUnits[this.period] ?
                this.options.periodUnits[this.period] : 'hour';
            this.chart.data.datasets[0].data = response.data;
            this.chart.data.datasets[0].label = response.label;
            this.chart.update();
        },

        /**
         * @returns {Object} chart object configuration
         */
        getChartSettings: function () {
            return {
                type: 'bar',
                data: {
                    datasets: [{
                        yAxisID: 'yAxis',
                        xAxisID: 'xAxis',
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
                        xAxis: {
                            offset: true,
                            type: 'time',
                            ticks: {
                                source: 'data'
                            }
                        },
                        yAxis: {
                            ticks: {
                                beginAtZero: true,
                                precision: this.options.precision
                            }
                        }
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
