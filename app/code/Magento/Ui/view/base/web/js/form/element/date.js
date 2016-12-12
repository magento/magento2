/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'moment',
    'mageUtils',
    './abstract',
    'moment-timezone-with-data'
], function (moment, utils, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            options: {},

            storeTimeZone: 'UTC',

            validationParams: {
                dateFormat: '${ $.outputDateFormat }'
            },

            /**
             * Format of date that comes from the
             * server (ICU Date Format).
             *
             * Used only in date picker mode
             * (this.options.showsTime == false).
             *
             * @type {String}
             */
            inputDateFormat: 'y-MM-dd',

            /**
             * Format of date that should be sent to the
             * server (ICU Date Format).
             *
             * Used only in date picker mode
             * (this.options.showsTime == false).
             *
             * @type {String}
             */
            outputDateFormat: 'MM/dd/y',

            /**
             * Date/time format that is used to display date in
             * the input field.
             *
             * @type {String}
             */
            pickerDateTimeFormat: '',

            pickerDefaultDateFormat: 'MM/dd/y', // ICU Date Format
            pickerDefaultTimeFormat: 'h:mm a', // ICU Time Format

            /**
             * Moment compatible format used for moment conversion
             * of date from datePicker
             */
            momentFormat: 'MM/DD/YYYY',

            elementTmpl: 'ui/form/element/date',

            listens: {
                'value': 'onValueChange',
                'shiftedValue': 'onShiftedValueChange'
            },

            /**
             * Date/time value shifted to corresponding timezone
             * according to this.storeTimeZone property. This value
             * will be sent to the server.
             *
             * @type {String}
             */
            shiftedValue: ''
        },

        /**
         * Initializes regular properties of instance.
         *
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();

            if (!this.options.dateFormat) {
                this.options.dateFormat = this.pickerDefaultDateFormat;
            }

            if (!this.options.timeFormat) {
                this.options.timeFormat = this.pickerDefaultTimeFormat;
            }

            this.prepareDateTimeFormats();

            return this;
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            return this._super().observe(['shiftedValue']);
        },

        /**
         * Prepares and sets date/time value that will be displayed
         * in the input field.
         *
         * @param {String} value
         */
        onValueChange: function (value) {
            var dateFormat,
                shiftedValue;

            if (value) {
                if (this.options.showsTime) {
                    shiftedValue = moment.tz(value, 'UTC').tz(this.storeTimeZone);
                } else {
                    dateFormat = this.shiftedValue() ? this.outputDateFormat : this.inputDateFormat;

                    shiftedValue = moment(value, dateFormat);
                }

                shiftedValue = shiftedValue.format(this.pickerDateTimeFormat);
            } else {
                shiftedValue = '';
            }

            if (shiftedValue !== this.shiftedValue()) {
                this.shiftedValue(shiftedValue);
            }
        },

        /**
         * Prepares and sets date/time value that will be sent
         * to the server.
         *
         * @param {String} shiftedValue
         */
        onShiftedValueChange: function (shiftedValue) {
            var value,
                formattedValue;

            if (shiftedValue) {
                if (this.options.showsTime) {
                    formattedValue = moment(shiftedValue).format('YYYY-MM-DD HH:mm');
                    value = moment.tz(formattedValue, this.storeTimeZone).tz('UTC').toISOString();
                } else {
                    value = moment(shiftedValue, this.momentFormat);
                    value = value.format(this.outputDateFormat);
                }
            } else {
                value = '';
            }

            if (value !== this.value()) {
                this.value(value);
            }
        },

        /**
         * Prepares and converts all date/time formats to be compatible
         * with moment.js library.
         */
        prepareDateTimeFormats: function () {
            this.pickerDateTimeFormat = this.options.dateFormat;
            this.momentFormat = this.options.dateFormat ?
                this.convertToMomentFormat(this.options.dateFormat) : this.momentFormat;

            if (this.options.showsTime) {
                this.pickerDateTimeFormat += ' ' + this.options.timeFormat;
            }

            this.pickerDateTimeFormat = utils.normalizeDate(this.pickerDateTimeFormat);

            if (this.dateFormat) {
                this.inputDateFormat = this.dateFormat;
            }

            this.inputDateFormat = utils.normalizeDate(this.inputDateFormat);
            this.outputDateFormat = utils.normalizeDate(this.outputDateFormat);

            this.validationParams.dateFormat = this.outputDateFormat;
        },

        /**
         * Converts PHP IntlFormatter format to moment format.
         *
         * @param {String} format - PHP format
         * @returns {String} - moment compatible formatting
         */
        convertToMomentFormat: function (format) {
            var newFormat;

            newFormat = format.replace(/yy|y/gi, 'YYYY'); // replace the year
            newFormat = newFormat.replace(/dd|d/g, 'DD'); // replace the date
            newFormat = newFormat.replace(/mm|m/g, 'MM'); //replace the month

            return newFormat;
        }
    });
});
