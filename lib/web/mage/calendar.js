/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*jshint browser:true jquery:true */
/*eslint max-depth: 0*/

(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'jquery/ui',
            'jquery/jquery-ui-timepicker-addon'
        ], factory);
    } else {
        factory(window.jQuery);
    }
}(function ($) {
    'use strict';

    var calendarBasePrototype,
        datepickerPrototype = $.datepicker.constructor.prototype;

    $.datepicker.markerClassName = '_has-datepicker';

    /**
     * Extend JQuery date picker prototype with store local time methods
     */
    $.extend(datepickerPrototype, {
        /**
         * Get date/time according to store settings.
         * We use serverTimezoneOffset (in seconds) instead of serverTimezoneSeconds
         * in order to have ability to know actual store time even if page hadn't been reloaded
         * @returns {Date}
         */
        _getTimezoneDate: function (options) {
            // local time in ms
            var ms = Date.now();

            options = options || $.calendarConfig || {};

            // Adjust milliseconds according to store timezone offset,
            // mind the GMT zero offset
            if (typeof options.serverTimezoneOffset !== 'undefined') {
                // Make UTC time and add store timezone offset in seconds
                ms += new Date().getTimezoneOffset() * 60 * 1000 + options.serverTimezoneOffset * 1000;
            } else if (typeof options.serverTimezoneSeconds !== 'undefined') {
                //Set milliseconds according to client local timezone offset
                ms = (options.serverTimezoneSeconds + new Date().getTimezoneOffset() * 60) * 1000;
            }

            return new Date(ms);
        },

        /**
         * Set date/time according to store settings.
         * @param {String|Object} target - the target input field or division or span
         */
        _setTimezoneDateDatepicker: function (target) {
            this._setDateDatepicker(target, this._getTimezoneDate());
        }
    });

    /**
     * Widget calendar
     */
    $.widget('mage.calendar', {

        /**
         * Merge global options with options passed to widget invoke
         * @protected
         */
        _create: function () {
            this._enableAMPM();
            this.options = $.extend(
                {},
                $.calendarConfig ? $.calendarConfig : {},
                this.options.showsTime ? {
                    showTime: true,
                    showHour: true,
                    showMinute: true
                } : {},
                this.options
            );
            this._initPicker(this.element);
            this._overwriteGenerateHtml();
        },

        /**
         * Get picker name
         * @protected
         */
        _picker: function () {
            return this.options.showsTime ? 'datetimepicker' : 'datepicker';
        },

        /**
         * Fix for Timepicker - Set ampm option for Timepicker if timeformat contains string 'tt'
         * @protected
         */
        _enableAMPM: function () {
            if (this.options.timeFormat && this.options.timeFormat.indexOf('tt') >= 0) {
                this.options.ampm = true;
            }
        },

        /**
         * Wrapper for overwrite jQuery UI datepicker function.
         */
        _overwriteGenerateHtml: function () {
            /**
             * Overwrite jQuery UI datepicker function.
             * Reason: magento date could be set before calendar show
             * but local date will be styled as current in original _generateHTML
             *
             * @param {Object} inst - instance datepicker.
             * @return {String} html template
             */
            $.datepicker.constructor.prototype._generateHTML = function (inst) {
                var today = this._getTimezoneDate(),
                    isRTL = this._get(inst, 'isRTL'),
                    showButtonPanel = this._get(inst, 'showButtonPanel'),
                    hideIfNoPrevNext = this._get(inst, 'hideIfNoPrevNext'),
                    navigationAsDateFormat = this._get(inst, 'navigationAsDateFormat'),
                    numMonths = this._getNumberOfMonths(inst),
                    showCurrentAtPos = this._get(inst, 'showCurrentAtPos'),
                    stepMonths = this._get(inst, 'stepMonths'),
                    isMultiMonth = parseInt(numMonths[0], 10) !== 1 || parseInt(numMonths[1], 10) !== 1,
                    currentDate = this._daylightSavingAdjust(!inst.currentDay ? new Date(9999, 9, 9) :
                        new Date(inst.currentYear, inst.currentMonth, inst.currentDay)),
                    minDate = this._getMinMaxDate(inst, 'min'),
                    maxDate = this._getMinMaxDate(inst, 'max'),
                    drawMonth = inst.drawMonth - showCurrentAtPos,
                    drawYear = inst.drawYear,
                    maxDraw,
                    prevText = this._get(inst, 'prevText'),
                    prev,
                    nextText = this._get(inst, 'nextText'),
                    next,
                    currentText = this._get(inst, 'currentText'),
                    gotoDate,
                    controls,
                    buttonPanel,
                    firstDay,
                    showWeek = this._get(inst, 'showWeek'),
                    dayNames = this._get(inst, 'dayNames'),
                    dayNamesMin = this._get(inst, 'dayNamesMin'),
                    monthNames = this._get(inst, 'monthNames'),
                    monthNamesShort =  this._get(inst, 'monthNamesShort'),
                    beforeShowDay = this._get(inst, 'beforeShowDay'),
                    showOtherMonths = this._get(inst, 'showOtherMonths'),
                    selectOtherMonths = this._get(inst, 'selectOtherMonths'),
                    defaultDate = this._getDefaultDate(inst),
                    html = '',
                    row = 0,
                    col = 0,
                    selectedDate,
                    cornerClass = ' ui-corner-all',
                    group = '',
                    calender = '',
                    dow = 0,
                    thead,
                    day,
                    daysInMonth,
                    leadDays,
                    curRows,
                    numRows,
                    printDate,
                    dRow = 0,
                    tbody,
                    daySettings,
                    otherMonth,
                    unselectable;

                if (drawMonth < 0) {
                    drawMonth += 12;
                    drawYear--;
                }

                if (maxDate) {
                    maxDraw = this._daylightSavingAdjust(new Date(maxDate.getFullYear(),
                        maxDate.getMonth() - numMonths[0] * numMonths[1] + 1, maxDate.getDate()));
                    maxDraw = minDate && maxDraw < minDate ? minDate : maxDraw;

                    while (this._daylightSavingAdjust(new Date(drawYear, drawMonth, 1)) > maxDraw) {
                        drawMonth--;

                        if (drawMonth < 0) {
                            drawMonth = 11;
                            drawYear--;

                        }
                    }
                }
                inst.drawMonth = drawMonth;
                inst.drawYear = drawYear;
                prevText = !navigationAsDateFormat ? prevText : this.formatDate(prevText,
                    this._daylightSavingAdjust(new Date(drawYear, drawMonth - stepMonths, 1)),
                    this._getFormatConfig(inst));
                prev = this._canAdjustMonth(inst, -1, drawYear, drawMonth) ?
                    '<a class="ui-datepicker-prev ui-corner-all" data-handler="prev" data-event="click"' +
                    ' title="' + prevText + '">' +
                    '<span class="ui-icon ui-icon-circle-triangle-' + (isRTL ? 'e' : 'w') + '">' +
                    '' + prevText + '</span></a>'
                    : hideIfNoPrevNext ? ''
                        :   '<a class="ui-datepicker-prev ui-corner-all ui-state-disabled" title="' +
                            '' + prevText + '"><span class="ui-icon ui-icon-circle-triangle-' +
                            '' + (isRTL ? 'e' : 'w') + '">' + prevText + '</span></a>';
                nextText = !navigationAsDateFormat ?
                    nextText
                    :   this.formatDate(nextText,
                        this._daylightSavingAdjust(new Date(drawYear, drawMonth + stepMonths, 1)),
                        this._getFormatConfig(inst));
                next = this._canAdjustMonth(inst, +1, drawYear, drawMonth) ?
                    '<a class="ui-datepicker-next ui-corner-all" data-handler="next" data-event="click"' +
                    'title="' + nextText + '"><span class="ui-icon ui-icon-circle-triangle-' +
                    '' + (isRTL ? 'w' : 'e') + '">' + nextText + '</span></a>'
                    : hideIfNoPrevNext ? ''
                        :   '<a class="ui-datepicker-next ui-corner-all ui-state-disabled" title="' + nextText + '">' +
                            '<span class="ui-icon ui-icon-circle-triangle-' + (isRTL ? 'w' : 'e') + '">' + nextText +
                            '</span></a>';
                gotoDate = this._get(inst, 'gotoCurrent') && inst.currentDay ? currentDate : today;
                currentText = !navigationAsDateFormat ? currentText :
                    this.formatDate(currentText, gotoDate, this._getFormatConfig(inst));
                controls = !inst.inline ?
                    '<button type="button" class="ui-datepicker-close ui-state-default ui-priority-primary ' +
                    'ui-corner-all" data-handler="hide" data-event="click">' +
                    this._get(inst, 'closeText') + '</button>'
                    : '';
                buttonPanel = showButtonPanel ?
                    '<div class="ui-datepicker-buttonpane ui-widget-content">' + (isRTL ? controls : '') +
                    (this._isInRange(inst, gotoDate) ? '<button type="button" class="ui-datepicker-current ' +
                    'ui-state-default ui-priority-secondary ui-corner-all" data-handler="today" data-event="click"' +
                    '>' + currentText + '</button>' : '') + (isRTL ? '' : controls) + '</div>' : '';
                firstDay = parseInt(this._get(inst, 'firstDay'), 10);
                firstDay = isNaN(firstDay) ? 0 : firstDay;

                for (row; row < numMonths[0]; row++) {
                    this.maxRows = 4;

                    for (col; col < numMonths[1]; col++) {
                        selectedDate = this._daylightSavingAdjust(new Date(drawYear, drawMonth, inst.selectedDay));

                        if (isMultiMonth) {
                            calender += '<div class="ui-datepicker-group';

                            if (numMonths[1] > 1) {
                                switch (col) {
                                    case 0: calender += ' ui-datepicker-group-first';
                                        cornerClass = ' ui-corner-' + (isRTL ? 'right' : 'left');
                                        break;

                                    case numMonths[1] - 1: calender += ' ui-datepicker-group-last';
                                        cornerClass = ' ui-corner-' + (isRTL ? 'left' : 'right');
                                        break;

                                    default: calender += ' ui-datepicker-group-middle'; cornerClass = '';
                                }
                            }
                            calender += '">';
                        }
                        calender += '<div class="ui-datepicker-header ' +
                            'ui-widget-header ui-helper-clearfix' + cornerClass + '">' +
                            (/all|left/.test(cornerClass) && parseInt(row, 10) === 0 ? isRTL ? next : prev : '') +
                            (/all|right/.test(cornerClass) && parseInt(row, 10) === 0 ? isRTL ? prev : next : '') +
                            this._generateMonthYearHeader(inst, drawMonth, drawYear, minDate, maxDate,
                            row > 0 || col > 0, monthNames, monthNamesShort) + // draw month headers
                            '</div><table class="ui-datepicker-calendar"><thead>' +
                            '<tr>';
                        thead = showWeek ?
                            '<th class="ui-datepicker-week-col">' + this._get(inst, 'weekHeader') + '</th>' : '';

                        for (dow; dow < 7; dow++) { // days of the week
                            day = (dow + firstDay) % 7;
                            thead += '<th' + ((dow + firstDay + 6) % 7 >= 5 ?
                                ' class="ui-datepicker-week-end"' : '') + '>' +
                                '<span title="' + dayNames[day] + '">' + dayNamesMin[day] + '</span></th>';
                        }
                        calender += thead + '</tr></thead><tbody>';
                        daysInMonth = this._getDaysInMonth(drawYear, drawMonth);

                        if (drawYear === inst.selectedYear && drawMonth === inst.selectedMonth) {
                            inst.selectedDay = Math.min(inst.selectedDay, daysInMonth);
                        }
                        leadDays = (this._getFirstDayOfMonth(drawYear, drawMonth) - firstDay + 7) % 7;
                        curRows = Math.ceil((leadDays + daysInMonth) / 7); // calculate the number of rows to generate
                        numRows = isMultiMonth ? this.maxRows > curRows ? this.maxRows : curRows : curRows;
                        this.maxRows = numRows;
                        printDate = this._daylightSavingAdjust(new Date(drawYear, drawMonth, 1 - leadDays));

                        for (dRow; dRow < numRows; dRow++) { // create date picker rows
                            calender += '<tr>';
                            tbody = !showWeek ? '' : '<td class="ui-datepicker-week-col">' +
                            this._get(inst, 'calculateWeek')(printDate) + '</td>';

                            for (dow = 0; dow < 7; dow++) { // create date picker days
                                daySettings = beforeShowDay ?
                                    beforeShowDay.apply(inst.input ? inst.input[0] : null, [printDate]) : [true, ''];
                                otherMonth = printDate.getMonth() !== drawMonth;
                                unselectable = otherMonth && !selectOtherMonths || !daySettings[0] ||
                                minDate && printDate < minDate || maxDate && printDate > maxDate;
                                tbody += '<td class="' +
                                ((dow + firstDay + 6) % 7 >= 5 ? ' ui-datepicker-week-end' : '') + // highlight weekends
                                (otherMonth ? ' ui-datepicker-other-month' : '') + // highlight days from other months
                                (printDate.getTime() === selectedDate.getTime() &&
                                drawMonth === inst.selectedMonth && inst._keyEvent || // user pressed key
                                defaultDate.getTime() === printDate.getTime() &&
                                defaultDate.getTime() === selectedDate.getTime() ?
                                    // or defaultDate is current printedDate and defaultDate is selectedDate
                                ' ' + this._dayOverClass : '') + // highlight selected day
                                (unselectable ? ' ' + this._unselectableClass + ' ui-state-disabled' : '') +
                                (otherMonth && !showOtherMonths ? '' : ' ' + daySettings[1] + // highlight custom dates
                                (printDate.getTime() === currentDate.getTime() ? ' ' + this._currentClass : '') +
                                (printDate.getDate() === today.getDate() && printDate.getMonth() === today.getMonth() &&
                                printDate.getYear() === today.getYear() ? ' ui-datepicker-today' : '')) + '"' +
                                ((!otherMonth || showOtherMonths) && daySettings[2] ?
                                ' title="' + daySettings[2] + '"' : '') + // cell title
                                (unselectable ? '' : ' data-handler="selectDay" data-event="click" data-month="' +
                                '' + printDate.getMonth() + '" data-year="' + printDate.getFullYear() + '"') + '>' +
                                (otherMonth && !showOtherMonths ? '&#xa0;' : // display for other months
                                    unselectable ? '<span class="ui-state-default">' + printDate.getDate() + '</span>'
                                        : '<a class="ui-state-default' +
                                    (printDate.getTime() === today.getTime() ? ' ' : '') +
                                    (printDate.getTime() === currentDate.getTime() ? ' ui-state-active' : '') +
                                    (otherMonth ? ' ui-priority-secondary' : '') +
                                    '" href="#">' + printDate.getDate() + '</a>') + '</td>';
                                printDate.setDate(printDate.getDate() + 1);
                                printDate = this._daylightSavingAdjust(printDate);
                            }
                            calender += tbody + '</tr>';
                        }
                        drawMonth++;

                        if (drawMonth > 11) {
                            drawMonth = 0;
                            drawYear++;
                        }
                        calender += '</tbody></table>' + (isMultiMonth ? '</div>' +
                        (numMonths[0] > 0 && col === numMonths[1] - 1 ? '<div class="ui-datepicker-row-break"></div>'
                            : '') : '');
                        group += calender;
                    }
                    html += group;
                }
                html += buttonPanel + ($.ui.ie6 && !inst.inline ?
                    '<iframe src="javascript:false;" class="ui-datepicker-cover" frameborder="0"></iframe>' : '');
                inst._keyEvent = false;

                return html;
            };
        },

        /**
         * Set current date if the date is not set
         * @protected
         * @param {Object} element
         */
        _setCurrentDate: function (element) {
            if (!element.val()) {
                element[this._picker()]('setTimezoneDate').val('');
            }
        },

        /**
         * Init Datetimepicker
         * @protected
         * @param {Object} element
         */
        _initPicker: function (element) {
            var picker = element[this._picker()](this.options),
                pickerButtonText = picker.next('.ui-datepicker-trigger')
                    .find('img')
                    .attr('title');

            picker.next('.ui-datepicker-trigger')
                .addClass('v-middle')
                .text('') // Remove jQuery UI datepicker generated image
                .append('<span>' + pickerButtonText + '</span>');
            this._setCurrentDate(element);
        },

        /**
         * destroy instance of datetimepicker
         */
        _destroy: function () {
            this.element[this._picker()]('destroy');
            this._super();
        },

        /**
         * Method is kept for backward compatibility and unit-tests acceptance
         * see \mage\calendar\calendar-test.js
         * @return {Object} date
         */
        getTimezoneDate: function () {
            return datepickerPrototype._getTimezoneDate.call(this, this.options);
        }
    });

    calendarBasePrototype = $.mage.calendar.prototype;

    /**
     * Extension for Calendar - date and time format convert functionality
     * @var {Object}
     */
    $.widget('mage.calendar', $.extend({}, calendarBasePrototype,
            /** @lends {$.mage.calendar.prototype} */ {
                /**
                 * key - backend format, value - jquery format
                 * @type {Object}
                 * @private
                 */
                dateTimeFormat: {
                    date: {
                        'EEEE': 'DD',
                        'EEE': 'D',
                        'EE': 'D',
                        'E': 'D',
                        'D': 'o',
                        'MMMM': 'MM',
                        'MMM': 'M',
                        'MM': 'mm',
                        'M': 'mm',
                        'yyyy': 'yy',
                        'y': 'yy',
                        'Y': 'yy',
                        'yy': 'yy' // Always long year format on frontend
                    },
                    time: {
                        'a': 'TT'
                    }
                },

                /**
                 * Add Date and Time converting to _create method
                 * @protected
                 */
                _create: function () {
                    if (this.options.dateFormat) {
                        this.options.dateFormat = this._convertFormat(this.options.dateFormat, 'date');
                    }

                    if (this.options.timeFormat) {
                        this.options.timeFormat = this._convertFormat(this.options.timeFormat, 'time');
                    }
                    calendarBasePrototype._create.apply(this, arguments);
                },

                /**
                 * Converting date or time format
                 * @protected
                 * @param {String} format
                 * @param {String} type
                 * @return {String}
                 */
                _convertFormat: function (format, type) {
                    var symbols = format.match(/([a-z]+)/ig),
                        separators = format.match(/([^a-z]+)/ig),
                        self = this,
                        convertedFormat = '';

                    if (symbols) {
                        $.each(symbols, function (key, val) {
                            convertedFormat +=
                                (self.dateTimeFormat[type][val] || val) +
                                (separators[key] || '');
                        });
                    }

                    return convertedFormat;
                }
            })
    );

    /**
     * Widget dateRange
     * @extends $.mage.calendar
     */
    $.widget('mage.dateRange', $.mage.calendar, {

        /**
         * creates two instances of datetimepicker for date range selection
         * @protected
         */
        _initPicker: function () {
            var from,
                to;

            if (this.options.from && this.options.to) {
                from = this.element.find('#' + this.options.from.id);
                to = this.element.find('#' + this.options.to.id);
                this.options.onSelect = $.proxy(function (selectedDate) {
                    to[this._picker()]('option', 'minDate', selectedDate);
                }, this);
                $.mage.calendar.prototype._initPicker.call(this, from);
                from.on('change', $.proxy(function () {
                    to[this._picker()]('option', 'minDate', from[this._picker()]('getDate'));
                }, this));
                this.options.onSelect = $.proxy(function (selectedDate) {
                    from[this._picker()]('option', 'maxDate', selectedDate);
                }, this);
                $.mage.calendar.prototype._initPicker.call(this, to);
                to.on('change', $.proxy(function () {
                    from[this._picker()]('option', 'maxDate', to[this._picker()]('getDate'));
                }, this));
            }
        },

        /**
         * destroy two instances of datetimepicker
         */
        _destroy: function () {
            if (this.options.from) {
                this.element.find('#' + this.options.from.id)[this._picker()]('destroy');
            }

            if (this.options.to) {
                this.element.find('#' + this.options.to.id)[this._picker()]('destroy');
            }
            this._super();
        }
    });

    // Overrides the "today" button functionality to select today's date when clicked.
    $.datepicker._gotoTodayOriginal = $.datepicker._gotoToday;

    /**
     * overwrite jQuery UI _showDatepicker function for proper HTML generation conditions.
     *
     */
    $.datepicker._showDatepickerOriginal = $.datepicker._showDatepicker;

    /**
     * Triggers original method showDataPicker for rendering calendar
     * @param {HTMLObject} input
     * @private
     */
    $.datepicker._showDatepicker = function (input) {
        if (!input.disabled) {
            $.datepicker._showDatepickerOriginal.call(this, input);
        }
    };

    /**
     * _gotoToday
     * @param {Object} el
     */
    $.datepicker._gotoToday = function (el) {
        //Set date/time according to timezone offset
        $(el).datepicker('setTimezoneDate')
            // To ensure that user can re-select date field without clicking outside it first.
            .blur();
    };

    return {
        dateRange:  $.mage.dateRange,
        calendar:   $.mage.calendar
    };
}));
