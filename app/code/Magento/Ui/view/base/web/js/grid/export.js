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
         * Redirect to built option url.
         */
        applyOption: function () {
            const option = this.getActiveOption();

            this.postRequest(option);
        },

        /**
         * Build option url.
         *
         * @param {Object} option
         * @returns {String}
         */
        postRequest: function (option) {
            let params = this.getParams(),
                data;

            if (!params) {
                return 'javascript:void(0);';
            }

            data = $.param(params);
            $.ajax({
                url: option.url,
                type: 'POST',
                data: data,
                showLoader: true,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function (exportedData, status, xhr) {
                    let a = document.createElement('a'),
                        blob,
                        url,
                        disposition = xhr.getResponseHeader('Content-Disposition'),
                        matches = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/),
                        fileName = matches && matches[1] ? matches[1].replace(/['"]/g, '') : '';

                    a.style = 'display: none';
                    document.body.appendChild(a);

                    blob = new Blob([exportedData], {
                        type: 'octet/stream'
                    });

                    url = window.URL.createObjectURL(blob);

                    a.href = url;
                    a.download = fileName;
                    a.click();

                    window.URL.revokeObjectURL(url);
                }
            });
        }
    });
});
