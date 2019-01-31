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
    './select'
], function (_, utils, Select) {
    'use strict';

    return Select.extend({
        defaults: {
            size: 5,
            elementTmpl: 'ui/form/element/multiselect',
            listens: {
                value: 'setDifferedFromDefault setPrepareToSendData'
            }
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
         * Sets the prepared data to dataSource
         * by path, where key is component link to dataSource with
         * suffix "-prepared-for-send"
         *
         * @param {Array} data - current component value
         */
        setPrepareToSendData: function (data) {
            if (_.isUndefined(data) || !data.length) {
                data = '';
            }

            this.source.set(this.dataScope + '-prepared-for-send', data);
        },

        /**
         * @inheritdoc
         */
        getInitialValue: function () {
            var values = [
                    this.normalizeData(this.source.get(this.dataScope)),
                    this.normalizeData(this.default)
                ],
                value;

            values.some(function (v) {
                return _.isArray(v) && (value = utils.copy(v)) && !_.isEmpty(v);
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
