/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'mageUtils',
    './abstract'
], function (_, utils, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            template: 'ui/form/element/checkbox-set',
            multiple: false,
            multipleScopeValue: null
        },

        /**
         * @inheritdoc
         */
        initConfig: function () {
            this._super();

            this.value = this.normalizeData(this.value);

            return this;
        },

        /**
         * @inheritdoc
         */
        initLinks: function () {
            var scope = this.source.get(this.dataScope);

            this.multipleScopeValue = this.multiple && _.isArray(scope) ? utils.copy(scope) : undefined;

            return this._super();
        },

        /**
         * @inheritdoc
         */
        reset: function () {
            this.value(utils.copy(this.initialValue));
            this.error(false);

            return this;
        },

        /**
         * @inheritdoc
         */
        clear: function () {
            var value = this.multiple ? [] : '';

            this.value(value);
            this.error(false);

            return this;
        },

        /**
         * @inheritdoc
         */
        normalizeData: function (value) {
            if (!this.multiple) {
                return this._super();
            }

            return _.isArray(value) ? utils.copy(value) : [];
        },

        /**
         * @inheritdoc
         */
        setInitialValue: function () {
            this._super();

            this.initialValue = utils.copy(this.initialValue);

            return this;
        },

        /**
         * @inheritdoc
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
         * @inheritdoc
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
