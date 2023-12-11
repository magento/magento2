/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './column',
    'underscore'
], function (Column, _) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/expandable',
            tooltipTmpl: 'ui/grid/cells/expandable/content',
            visibeItemsLimit: 5,
            tooltipTitle: ''
        },

        /**
         * Gets label from full options array.
         *
         * @param {Object} record - Record object.
         * @returns {String}
         */
        getFullLabel: function (record) {
            return this.getLabelsArray(record).join(', ');
        },

        /**
         * Gets label from options array limited by 'visibeItemsLimit'.
         *
         * @param {Object} record - Record object.
         * @returns {String}
         */
        getShortLabel: function (record) {
            return this.getLabelsArray(record).slice(0, this.visibeItemsLimit).join(', ');
        },

        /**
         * Extracts array of labels associated with provided values and sort it alphabetically.
         *
         * @param {Object} record - Record object.
         * @returns {Array}
         */
        getLabelsArray: function (record) {
            var values = this.getLabel(record),
                options = this.options || [],
                labels = [];

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
                    labels.push(item.label);
                }
            });

            return labels.sort(
                function (labelFirst, labelSecond) {
                    return labelFirst.toLowerCase().localeCompare(labelSecond.toLowerCase());
                }
            );
        },

        /**
         * Transformation tree options structure to liner array.
         *
         * @param {Array} options
         * @returns {Array}
         */
        flatOptions: function (options) {
            var self = this;

            return options.reduce(function (opts, option) {
                if (_.isArray(option.value)) {
                    opts = opts.concat(self.flatOptions(option.value));
                } else {
                    opts.push(option);
                }

                return opts;
            }, []);
        },

        /**
         * Checks if amount of options is more than limit value.
         *
         * @param {Object} record - Data to be preprocessed.
         * @returns {Boolean}
         */
        isExpandable: function (record) {
            return this.getLabel(record).length > this.visibeItemsLimit;
        }
    });
});
