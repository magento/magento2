/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'underscore',
    'uiElement'
], function ($, _, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'ui/grid/exportButton',
            selectProvider: 'ns = ${ $.ns }, index = ids',
            checked: '',
            additionalParams: [],
            modules: {
                selections: '${ $.selectProvider }'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            this._super()
                .initChecked();
        },

        /** @inheritdoc */
        initConfig: function () {
            this._super();

            _.each(this.additionalParams, function (value, key) {
                key = 'additionalParams.' + key;
                this.imports[key] = value;
            }, this);

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('checked');

            return this;
        },

        /**
         * Checks first option if checked not defined.
         *
         * @returns {Object}
         */
        initChecked: function () {
            if (!this.checked()) {
                this.checked(
                    this.options[0].value
                );
            }

            return this;
        },

        /**
         * Compose params object that will be added to request.
         *
         * @returns {Object}
         */
        getParams: function () {
            var selections = this.selections(),
                data = selections ? selections.getSelections() : null,
                itemsType,
                result = {};

            if (data) {
                itemsType = data.excludeMode ? 'excluded' : 'selected';
                result.filters = data.params.filters;
                result.search = data.params.search;
                result.namespace = data.params.namespace;
                result[itemsType] = data[itemsType];
                _.each(this.additionalParams, function (param, key) {
                    result[key] = param;
                });

                if (!result[itemsType].length) {
                    result[itemsType] = false;
                }
            }

            return result;
        },

        /**
         * Find checked option.
         *
         * @returns {Object}
         */
        getActiveOption: function () {
            return _.findWhere(this.options, {
                value: this.checked()
            });
        },

        /**
         * Build option url.
         *
         * @param {Object} option
         * @returns {String}
         */
        buildOptionUrl: function (option) {
            var params = this.getParams();

            if (!params) {
                return 'javascript:void(0);';
            }

            return option.url + '?' + $.param(params);
            //TODO: MAGETWO-40250
        },

        /**
         * Redirect to built option url.
         */
        applyOption: function () {
            var option = this.getActiveOption(),
                url = this.buildOptionUrl(option);

            location.href = url;

        }
    });
});
