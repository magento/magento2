/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
define([
    'jquery',
    'Magento_Backend/js/dashboard/chart'
], function ($) {
    'use strict';

    describe('Magento_Backend/js/dashboard/chart', function () {
        let chartContainer, canvas;
        const dataProvider = {
            'today': {
                label: 'Revenue',
                data: [
                    {
                        'x': '2026-01-10 06:00',
                        'y': 80
                    }
                ]
            },
            '1m': {
                label: 'Revenue',
                data: [

                    {
                        'x': '2026-01-09',
                        'y': 120
                    },
                    {
                        'x': '2026-01-10',
                        'y': 80
                    }
                ]
            },
            '1y': {
                label: 'Revenue',
                data: [
                    {
                        'x': '2026-01',
                        'y': 200
                    }
                ]
            }
        };

        /**
         * Creates a new instance of the dashboard chart widget with a mocked _request method.
         *
         * @param {jQuery} element
         * @param {Object} [options={}]
         * @param {Object} [mocks={}]
         * @return {*}
         */
        function getWidgetInstance(element, options, mocks) {
            $.widget('test.dashboardChartTest', $.mage.dashboardChart, $.extend({
                _request: function (data) {
                    return $.Deferred().resolve(dataProvider[data.period || 'today']);
                }
            }, mocks || {}));

            return element.dashboardChartTest($.extend({
                updateUrl: '/test/url',
                type: 'bar',
                periodSelect: '#' + element.parent().parent().attr('id') + ' select',
                periodUnits: {
                    'today': 'hour',
                    '1m': 'day',
                    '1y': 'month'
                }
            }, options || {})).data('test-dashboardChartTest');
        }

        beforeEach(function () {
            chartContainer = $('<div id="' + Math.random().toString().substr(2) + '"></div>')
                .append(
                    '<select>' +
                        '<option value="today" selected>Today</option>' +
                        '<option value="1m">Current Month</option>' +
                        '<option value="1y">YTD</option>' +
                    '</select>'
                )
                .append('<div><canvas></canvas></div><div class="dashboard-diagram-nodata">No Data</div>')
                .appendTo($('body'));
            canvas = chartContainer.find('canvas');
        });

        afterEach(function () {
            chartContainer.remove();
        });

        it('should create dashboardChart widget', function () {
            expect($.fn.dashboardChart).toBeDefined();
        });

        it('should hide the chart and show "No Data" text if data is not available', () => {
            const period = 'today',
                chartWidget = getWidgetInstance(canvas, {}, {
                    _request: function () {
                        return $.Deferred().resolve({label: 'Revenue', data: []});
                    }
                });

            expect(chartWidget.period).toBe(period);
            expect(chartWidget.chart).toBeDefined();
            expect(chartWidget.chart.data.datasets[0].label).toBe('Revenue');
            expect(chartWidget.chart.data.datasets[0].data).toEqual([]);
            expect(canvas.parent().is(':visible')).toBeFalse();
            expect(canvas.parent().next('.dashboard-diagram-nodata').is(':visible')).toBeTrue();
        });
        it('should create a chart with default period', () => {
            const period = 'today',
                chartWidget = getWidgetInstance(canvas);

            expect(chartWidget.period).toBe(period);
            expect(chartWidget.chart).toBeDefined();
            expect(chartWidget.chart.data.datasets[0].label).toBe(dataProvider[period].label);
            expect(chartWidget.chart.data.datasets[0].data).toBe(dataProvider[period].data);
            expect(canvas.parent().is(':visible')).toBeTrue();
            expect(canvas.parent().next('.dashboard-diagram-nodata').is(':visible')).toBeFalse();
        });
        it('should update the chart when period changes', () => {
            const period = '1m',
                chartWidget = getWidgetInstance(canvas);

            expect(chartWidget.period).toBe('today');
            expect(chartWidget.chart).toBeDefined();

            canvas.parent().parent().find('select').val(period).trigger('change');

            expect(chartWidget.period).toBe(period);
            expect(chartWidget.chart.data.datasets[0].label).toBe(dataProvider[period].label);
            expect(chartWidget.chart.data.datasets[0].data).toBe(dataProvider[period].data);
            expect(canvas.parent().is(':visible')).toBeTrue();
            expect(canvas.parent().next('.dashboard-diagram-nodata').is(':visible')).toBeFalse();
        });
    });
});
