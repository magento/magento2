/**
 * Copyright © 2016 Magento. All rights reserved.
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

            this.multipleScopeValue = _.isArray(scope) ? utils.copy(scope) : undefined;

            return this._super();
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
        normalizeData: function (value) {
            if (utils.isEmpty(value)) {
                value = [];
            }

            return _.isString(value) ? value.split(',') : value;
        },

        /**
         * @inheritdoc
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
         * @inheritdoc
         */
        hasChanged: function () {
            var value = this.value(),
                initial = this.initialValue;

            return !utils.equalArrays(value, initial);
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
            this.value([]);
            this.error(false);

            return this;
        }
    });
});
