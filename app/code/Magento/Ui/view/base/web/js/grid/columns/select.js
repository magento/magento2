/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    './column'
], function (_, Column) {
    'use strict';

    return Column.extend({
        /**
         * Retrieves label associated with a provided value.
         *
         * @returns {String}
         */
        getLabel: function () {
            var options = this.options || [],
                values = this._super(),
                multiSelectValues = [],
                label = [];

            if (_.isString(values)) {
                multiSelectValues = values.split(',');

                multiSelectValues = multiSelectValues.map(function (value) {
                    return value + '';
                });
            }

            if (!_.isArray(values)) {
                values = [values];
            }

            values = values.map(function (value) {
                return value + '';
            });

            options = this.flatOptions(options);

            options.forEach(function (item) {
                if (_.contains(values, item.value + '')) {
                    label.push(item.label);
                } else if (_.contains(multiSelectValues, item.value + '')) {
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

            if (!_.isArray(options)) {
                options = _.values(options);
            }

            return options.reduce(function (opts, option) {
                if (_.isArray(option.value)) {
                    opts = opts.concat(self.flatOptions(option.value));
                } else {
                    opts.push(option);
                }

                return opts;
            }, []);
        }
    });
});
