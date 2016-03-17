/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    './abstract'
], function (_, utils, registry, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            template: 'ui/form/element/checkbox-set',
            multiple: false,
            multipleScopeValue: null
        },

        /**
         * Initializes configuration.
         *
         * @returns {CheckboxSet} Chainable.
         */
        initConfig: function () {
            this._super();

            this.value = this.normalizeData(this.value);

            return this.setMultipleScopeValue();
        },

        /**
         * Defines initial value.
         *
         * @returns {CheckboxSet} Chainable.
         */
        setInitialValue: function () {
            this._super();

            this.initialValue = utils.copy(this.initialValue);

            return this;
        },

        /**
         * Caches value from dataProvider for next proper assignment.
         *
         * @returns {CheckboxSet} Chainable.
         */
        setMultipleScopeValue: function () {
            var provider = registry.get(this.provider),
                scope = provider.get(this.dataScope);

            this.multipleScopeValue = this.multiple && _.isArray(scope) ? utils.copy(scope) : undefined;

            return this;
        },

        /**
         * Restores initial value.
         *
         * @returns {CheckboxSet} Chainable.
         */
        reset: function () {
            this.value(utils.copy(this.initialValue));
            this.error(false);

            return this;
        },

        /**
         * Empties current value.
         *
         * @returns {CheckboxSet} Chainable.
         */
        clear: function () {
            var value = this.multiple ? [] : '';

            this.value(value);
            this.error(false);

            return this;
        },

        /**
         * Performs data type conversions.
         *
         * @param {*} value
         * @returns {Array|String}
         */
        normalizeData: function (value) {
            if (!this.multiple) {
                return this._super();
            }

            return _.isArray(value) ? utils.copy(value) : [];
        },

        /**
         * Gets initial value of element
         *
         * @returns {*} Elements' value.
         */
        getInitialValue: function () {
            var values = [this.multipleScopeValue, this.default, this.value.peek(), []],
                value;

            if (!this.multiple) {
                return this._super();
            }

            values.some(function (v) {
                return _.isArray(v) && (value = utils.copy(v));
            });

            return value;
        },

        /**
         * Returns labels which matches current value.
         *
         * @returns {String|Array}
         */
        getPreview: function () {
            var option;

            if (!this.multiple) {
                option = this.getOption(this.value());

                return option ? option.label : '';
            }

            return this.value.map(function (value) {
                return this.getOption(value).label;
            }, this);
        },

        /**
         * Returns option object assoctiated with provided value.
         *
         * @param {String} value
         * @returns {Object}
         */
        getOption: function (value) {
            return _.findWhere(this.options, {
                value: value
            });
        },

        /**
         * Defines if current value has
         * changed from its' initial state.
         *
         * @returns {Boolean}
         */
        hasChanged: function () {
            var value = this.value(),
                initial = this.initialValue;

            return this.multiple ?
                !utils.equalArrays(value, initial) :
                this._super();
        }
    });
});
