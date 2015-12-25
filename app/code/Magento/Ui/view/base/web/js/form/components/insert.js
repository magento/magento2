/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'jquery',
    'mage/translate',
    'mageUtils',
    'underscore',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/lib/view/utils/bindings',
    'Magento_Ui/js/lib/view/utils/async'
], function (Element, $, $t, utils, _, alert) {
    'use strict';

    return Element.extend({
        defaults: {
            content: '',
            template: 'ui/form/insert',
            showSpinner: true,
            loading: false,
            contentSelector: '${$.name}',
            externalTransfer: false,
            internalTransfer: false,
            externalData: [],
            deps: '${ $.externalProvider }',
            params: {
                namespace: '${ $.ns }'
            },
            renderSettings: {
                url: '${ $.render_url }',
                dataType: 'html'
            },
            imports: {},
            exports: {},
            listens: {},
            links: {
                value: '${ $.provider }:${ $.dataScope}'
            },
            modules: {
                externalSource: '${ $.externalProvider }'
            }
        },

        /** @inheritdoc */
        initialize: function () {
            var self = this._super();

            _.bindAll(this, 'onRender');

            $.async('.' + this.contentSelector, function (el) {
                self.contentEl = $(el);
                self.render();
            });

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            return this._super()
                .observe([
                    'content',
                    'value',
                    'loading'
                ]);
        },

        /** @inheritdoc */
        initConfig: function (config) {
            this.initTransferConfig(config)._super();
            this.contentSelector = this.contentSelector.replace(/\./g, '_');

            return this;
        },

        /**
         * Sync data with external provider.
         *
         * @param {Object} config
         * @returns {Object}
         */
        initTransferConfig: function (config) {
            var key, value;

            if (config.externalTransfer) {
                _.each(config.externalData, function (val) {
                    value = val;
                    key = 'externalValue.' + val.replace('data.', '');
                    this.links[key] = '${ $.externalProvider }:' + value;
                }, this.constructor.defaults);
            }

            if (config.internalTransfer) {
                this.constructor.defaults.links.externalValue = 'value';
            }

            return this;
        },

        /**
         * Request for render content.
         *
         * @returns {Object}
         */
        render: function () {
            var request = this.requestData(this.params, this.renderSettings);

            request
                .done(this.onRender)
                .fail(this.onError);

            return request;
        },

        /**
         * Request with configurable params and settings.
         *
         * @param {Object} params
         * @param {Object} ajaxSettings
         * @returns {Object}
         */
        requestData: function (params, ajaxSettings) {
            var query = utils.copy(params);

            ajaxSettings = _.extend({
                url: this.updateUrl,
                method: 'GET',
                data: query,
                dataType: 'json'
            }, ajaxSettings);

            this.loading(true);

            return $.ajax(ajaxSettings);
        },

        /**
         * Callback that render content.
         *
         * @param {*} data
         */
        onRender: function (data) {
            this.loading(false);
            this.set('content', data);
            this.contentEl.children().applyBindings();
            this.contentEl.trigger('contentUpdated');
        },

        /**
         * Error callback.
         *
         * @param {Object} xhr
         */
        onError: function (xhr) {
            if (xhr.statusText === 'abort') {
                return;
            }

            alert({
                content: $t('Something went wrong.')
            });
        },

        /**
         * Getter for external data.
         *
         * @returns {Object}
         */
        getExternalData: function () {
            var data = {};

            _.each(this.externalData, function (path) {
                utils.nested(data, path.replace('data.', ''), this.externalSource().get(path));
            }, this);

            return data;
        }
    });
});
