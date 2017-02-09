/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    './column'
], function (_, Column) {
    'use strict';

    return Column.extend({

        /*eslint-disable eqeqeq*/
        /**
         * Retrieves label associated with a provided value.
         *
         * @returns {String}
         */
        getLabel: function () {
            var options = this.options || [],
                values = this._super(),
                label = [];

            if (_.isString(values)) {
                values = values.split(',');
            }

            if (!Array.isArray(values)) {
                values = [values];
            }

            values = values.map(function (value) {
                return value + '';
            });

            options = this.flatOptions(options);

            options.forEach(function (item) {
                if (_.contains(values, item.value + '')) {
                    label.push(item.label);
                }
            });

            return label.join(', ');
        },

        /**
         * Transformation tree options structure to liner array.
         *
         * @param {Array} options
         * @returns {Array}
         */
        flatOptions: function (options) {
            var self = this;

            return options.reduce(function (options, option) {
                if (_.isArray(option.value)) {
                    options = options.concat(self.flatOptions(option.value));
                } else {
                    options.push(option);
                }

                return options;
            }, []);
        }

        /*eslint-enable eqeqeq*/
    });
});
