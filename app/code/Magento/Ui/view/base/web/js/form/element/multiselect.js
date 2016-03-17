/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    './select'
], function (_, utils, registry, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            size: 5,
            elementTmpl: 'ui/form/element/multiselect'
        },

        /**
         * Initializes configuration.
         *
         * @returns {MultiSelect} Chainable.
         */
        initConfig: function () {
            this._super();

            this.value = this.normalizeData(this.value);

            return this.setMultipleScopeValue();
        },

        /**
         * Defines initial value.
         *
         * @returns {MultiSelect} Chainable.
         */
        setInitialValue: function () {
            this._super();

            this.initialValue = utils.copy(this.initialValue);

            return this;
        },

        /**
         * Caches value from dataProvider for next proper assignment.
         *
         * @returns {MultiSelect} Chainable.
         */
        setMultipleScopeValue: function () {
            var provider = registry.get(this.provider),
                scope = provider.get(this.dataScope);

            this.multipleScopeValue = _.isArray(scope) ? utils.copy(scope) : undefined;

            return this;
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
         * Gets initial value of element
         *
         * @returns {*} Elements' value.
         */
        getInitialValue: function () {
            var values = [this.multipleScopeValue, this.default, this.value.peek(), []],
                value;

            values.some(function (v) {
                return _.isArray(v) && (value = utils.copy(v));
            });

            return value;
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
            this.error(false);

            return this;
        },

        /**
         * Empties current value.
         *
         * @returns {MultiSelect} Chainable.
         */
        clear: function () {
            this.value([]);
            this.error(false);

            return this;
        }
    });
});
