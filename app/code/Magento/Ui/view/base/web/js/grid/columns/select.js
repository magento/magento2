/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    './column'
], function (Column) {
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
                label = '',
                value = this._super();

            options.some(function (item) {
                label = item.label;

                return item.value == value;
            });

            return label;
        }

        /*eslint-enable eqeqeq*/
    });
});
