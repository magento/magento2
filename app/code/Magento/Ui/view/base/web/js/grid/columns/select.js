/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './column'
], function (Column) {
    'use strict';

    return Column.extend({
        /**
         * Retrieves label associated with a provided value.
         *
         * @param {(String|Number)} value - Value of the option.
         * @returns {String}
         */
        getLabel: function (value) {
            var options = this.options || [],
                label = '';

            value = value || '';

            /*eslint-disable eqeqeq*/
            options.some(function (item) {
                label = item.label;

                return item.value == value;
            });
            /*eslint-enable eqeqeq*/

            return label;
        }
    });
});
