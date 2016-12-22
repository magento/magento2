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

            elementTmpl: 'ui/form/element/date',

            /**
             * Format needed by moment timezone for conversion
             */
            timezoneFormat: 'YYYY-MM-DD HH:mm',

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
                formattedValue,
                momentDateTime;

            if (shiftedValue) {
                if (this.options.showsTime) {
                    momentDateTime = moment(shiftedValue, this.pickerDateTimeFormat);
                    formattedValue = moment(momentDateTime).format(this.timezoneFormat);
                    value = moment.tz(formattedValue, this.storeTimeZone).tz('UTC').toISOString();
                } else {
                    value = moment(shiftedValue, this.pickerDateTimeFormat);
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

            if (this.options.showsTime) {
                this.pickerDateTimeFormat += ' ' + this.options.timeFormat;
            }

            this.pickerDateTimeFormat = utils.convertToMomentFormat(this.pickerDateTimeFormat);

            if (this.dateFormat) {
                this.inputDateFormat = this.dateFormat;
            }

            this.inputDateFormat = utils.convertToMomentFormat(this.inputDateFormat);
            this.outputDateFormat = utils.convertToMomentFormat(this.outputDateFormat);

            this.validationParams.dateFormat = this.outputDateFormat;
        }
    });
});
