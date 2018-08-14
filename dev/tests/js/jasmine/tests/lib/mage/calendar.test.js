/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'jquery/ui',
    'mage/calendar'
], function ($) {
    'use strict';

    describe('mage/calendar', function () {
        describe('Check calendar', function () {
            var calendarSelector = '#calendar';

            beforeEach(function () {
                var $calendar = $('<input type="text" id="calendar" />');

                $('body').append($calendar);
            });

            afterEach(function () {
                $(calendarSelector).remove();
                $(calendarSelector).calendar('destroy');
            });

            it('Check that calendar inited', function () {
                var $calendar = $(calendarSelector).calendar();

                expect($calendar.is(':mage-calendar')).toBe(true);
            });

            it('Check configuration merge', function () {
                var $calendar;

                $.extend(true, $, {
                    calendarConfig: {
                        showOn: 'button',
                        showAnim: '',
                        buttonImageOnly: true,
                        showButtonPanel: true,
                        showWeek: true,
                        timeFormat: '',
                        showTime: false,
                        showHour: false,
                        showMinute: false
                    }
                });

                $calendar = $(calendarSelector).calendar();

                expect($calendar.calendar('option', 'showOn')).toBe('button');
                expect($calendar.calendar('option', 'showAnim')).toBe('');
                expect($calendar.calendar('option', 'buttonImageOnly')).toBe(true);
                expect($calendar.calendar('option', 'showButtonPanel')).toBe(true);
                expect($calendar.calendar('option', 'showWeek')).toBe(true);
                expect($calendar.calendar('option', 'timeFormat')).toBe('');
                expect($calendar.calendar('option', 'showTime')).toBe(false);
                expect($calendar.calendar('option', 'showHour')).toBe(false);
                expect($calendar.calendar('option', 'showMinute')).toBe(false);

                delete $.calendarConfig;
            });

            it('Specifying AM/PM in timeformat option changes AMPM option to true', function () {
                var $calendar = $(calendarSelector).calendar({
                    timeFormat: 'hh:mm tt',
                    ampm: false
                });

                expect($calendar.calendar('option', 'ampm')).toBe(true);
            });

            it('Omitting AM/PM in timeformat option changes AMPM option to false', function () {
                var $calendar = $(calendarSelector).calendar({
                    timeFormat: 'hh:mm'
                });

                expect($calendar.calendar('option', 'ampm')).toBe(null);
            });

            it('With server timezone offset', function () {
                var serverTimezoneSeconds = 1346122095,
                    $calendar = $(calendarSelector).calendar({
                        serverTimezoneSeconds: serverTimezoneSeconds
                    }),
                    currentDate = new Date();

                currentDate.setTime((serverTimezoneSeconds + currentDate.getTimezoneOffset() * 60) * 1000);

                expect($calendar.calendar('getTimezoneDate').toString()).toBe(currentDate.toString());
            });

            it('Without sever timezone offset', function () {
                var $calendar = $(calendarSelector).calendar(),
                    currentDate = new Date();

                expect($calendar.calendar('getTimezoneDate').toString()).toBe(currentDate.toString());
            });

            it('Check destroy', function () {
                var $calendar = $(calendarSelector).calendar();

                expect($calendar.is(':mage-calendar')).toBe(true);
                $calendar.calendar('destroy');
                expect($calendar.is(':mage-calendar')).toBe(false);
            });
        });
        describe('Check dateRange', function () {
            var dateRangeSelector = '#date-range';

            beforeEach(function () {
                var $dateRange = $('<div id="date-range">' +
                    '<input type="text" id="from" />' +
                    '<input type="text" id="to" />' +
                    '</div>');

                $('body').append($dateRange);
            });

            afterEach(function () {
                $(dateRangeSelector).remove();
                $(dateRangeSelector).dateRange('destroy');
            });

            it('Check that dateRange inited', function () {
                var $dateRange = $(dateRangeSelector).dateRange();

                expect($dateRange.is(':mage-dateRange')).toBe(true);
            });

            it('Check that dateRange inited with additional options', function () {
                var $from = $('#from'),
                    $to = $('#to');

                $(dateRangeSelector).dateRange({
                    from: {
                        id: 'from'
                    },
                    to: {
                        id: 'to'
                    }
                });

                expect($from.hasClass('_has-datepicker')).toBe(true);
                expect($to.hasClass('_has-datepicker')).toBe(true);
            });

            it('Check destroy', function () {
                var $dateRange = $(dateRangeSelector).dateRange({
                        from: {
                            id: 'from'
                        },
                        to: {
                            id: 'to'
                        }
                    }),
                    $from = $('#from'),
                    $to = $('#to');

                expect($dateRange.is(':mage-dateRange')).toBe(true);
                expect($from.hasClass('_has-datepicker')).toBe(true);
                expect($to.hasClass('_has-datepicker')).toBe(true);
                $dateRange.dateRange('destroy');
                expect($dateRange.is(':mage-dateRange')).toBe(false);
                expect($from.hasClass('_has-datepicker')).toBe(false);
                expect($to.hasClass('_has-datepicker')).toBe(false);
            });
        });
    });
});
