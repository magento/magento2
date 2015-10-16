/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    './select'
], function (_, utils, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            size: 5
        },

        /**
         * Splits incoming string value.
         *
         * @returns {Array}
         */
        normalizeData: function () {
            var value = this._super();

            return _.isString(value) ? value.split(',') : value;
        },

        /**
         * Defines if value has changed
         *
         * @returns {Boolean}
         */
        hasChanged: function () {
            var value = this.value(),
                initial = this.initialValue;

            return !utils.equalArrays(value, initial);
        }
    });
});
