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
         * @param {Array} values - Values of the option.
         * @returns {String}
         */
        getLabel: function (values) {
            var options = this.options || [],
                labels = [];

            values = values || [];

            /*eslint-disable eqeqeq*/
            options.forEach(function (item) {
                if(values.indexOf(item.value) > -1) {
                    labels.push(item.label);
                }
            });
            /*eslint-enable eqeqeq*/

            return labels.join(', ');
        }
    });
});
