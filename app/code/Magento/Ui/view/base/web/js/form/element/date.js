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

            timeFormat: 'HH:mm', // ICU Time Format
            dateFormat: 'dd/MM/yyyy', // ICU Date Format

            elementTmpl: 'ui/form/element/date',

            listens: {
                'value': 'onValueChange',
                'shiftedValue': 'onShiftedValueChange'
            }
        },

        /**
         * Date/time value shifted to corresponding timezone
         * according to this.timeOffset property.
         *
         * @type {String}
         */
        shiftedValue: '',

        /**
         * Date/time format converted to be compatible with
         * moment.js library.
         *
         * @type {String}
         */
        momentDatetimeFormat: '',

        /**
         * Initializes regular properties of instance.
         *
         * @returns {Object} Chainable.
         */
        initConfig: function () {
            this._super();

            var options = {
                timeFormat: this.timeFormat,
                dateFormat: this.dateFormat
            };

            var datetimeFormat = this.dateFormat + ' ' + this.timeFormat;

            this.momentDatetimeFormat = utils.normalizeDate(datetimeFormat);

            jQuery.extend(this.options, options);

            return this;
        },

        /**
         * @inheritdoc
         */
        initObservable: function () {
            return this._super().observe(['shiftedValue']);
        },

        /**
         * Formats provided value according to 'dateFormat' property.
         *
         * @returns {String}
         */
        normalizeData: function () {
            var value = this._super();

            if (value) {
                value = moment(value).format(this.momentDatetimeFormat);
            }

            return value;
        },

        /**
         * Prepares and sets 'shifted' (that only will be displayed
         * on frontend) date/time value.
         *
         * @param {String} value
         */
        onValueChange: function (value) {
            if (value) {
                var shiftedValue = moment.utc(value).add(this.timeOffset, 'seconds');

                shiftedValue = shiftedValue.format(this.momentDatetimeFormat);

                if (shiftedValue !== this.shiftedValue()) {
                    this.shiftedValue(shiftedValue);
                }
            }
        },

        /**
         * Prepares and sets 'real' (that will be sent to backend)
         * date/time value.
         *
         * @param {String} shiftedValue
         */
        onShiftedValueChange: function (shiftedValue) {
            if (shiftedValue) {
                var value = moment.utc(shiftedValue, this.momentDatetimeFormat);

                value = value.subtract(this.timeOffset, 'seconds').toISOString();

                if (value !== this.value()) {
                    this.value(value);
                }
            }
        }
    });
});
