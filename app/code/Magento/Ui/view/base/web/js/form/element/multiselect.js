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
            size: 5,
            elementTmpl: 'ui/form/element/multiselect'
        },

        /**
         * Splits incoming string value.
         *
         * @returns {Array}
         */
        normalizeData: function (value) {
            if (utils.isEmpty(value)) {
                value = [];
            }

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
        },

        /**
         * Restores initial value.
         *
         * @returns {MultiSelect} Chainable.
         */
        reset: function () {
            this.value(utils.copy(this.initialValue));

            return this;
        },

        /**
         * Empties current value.
         *
         * @returns {MultiSelect} Chainable.
         */
        clear: function () {
            this.value([]);

            return this;
        }
    });
});
