/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'moment',
    'mageUtils',
    './abstract'
], function (moment, utils, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            options: {},

            timeOffset: 0,

            showsTime: false,

            dateFormat: 'MM/dd/y', // ICU Date Format
            timeFormat: 'HH:mm', // ICU Time Format
            validationParams: {
                dateFormat: '${ $.outputDateFormat }'
            },

            /**
             * Format of date that comes from the
             * server (ICU Date Format).
             *
             * Used only in date picker mode
             * (this.showsTime == false).
             *
             * @type {String}
             */
            inputDateFormat: 'y-MM-dd',

            /**
             * Format of date that should be sent to the
             * server (ICU Date Format).
             *
             * Used only in date picker mode
             * (this.showsTime == false).
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
            datetimeFormat: '',

            elementTmpl: 'ui/form/element/date',

            listens: {
                'value': 'onValueChange',
                'shiftedValue': 'onShiftedValueChange'
            },

            /**
             * Date/time value shifted to corresponding timezone
             * according to this.timeOffset property. This value
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

            utils.extend(this.options, {
                showsTime: this.showsTime,
                timeFormat: this.timeFormat,
                dateFormat: this.dateFormat
            });

            this.prepareDatetimeFormats();

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
                if (this.showsTime) {
                    shiftedValue = moment.utc(value).add(this.timeOffset, 'seconds');
                } else {
                    dateFormat = this.shiftedValue() ? this.outputDateFormat : this.inputDateFormat;

                    shiftedValue = moment(value, dateFormat);
                }

                shiftedValue = shiftedValue.format(this.datetimeFormat);
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
            var value;

            if (shiftedValue) {

                if (this.showsTime) {
                    value = moment.utc(shiftedValue, this.datetimeFormat);
                    value = value.subtract(this.timeOffset, 'seconds').toISOString();
                } else {
                    value = moment(shiftedValue, this.datetimeFormat);
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
        prepareDatetimeFormats: function () {
            this.datetimeFormat = this.dateFormat;

            if (this.showsTime) {
                this.datetimeFormat += ' ' + this.timeFormat;
            }

            this.datetimeFormat = utils.normalizeDate(this.datetimeFormat);

            this.inputDateFormat = utils.normalizeDate(this.inputDateFormat);
            this.outputDateFormat = utils.normalizeDate(this.outputDateFormat);
            this.validationParams.dateFormat = this.outputDateFormat;
        }
    });
});
