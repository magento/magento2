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
            autoRender: true,
            contentSelector: '${$.name}',
            externalData: [],
            params: {
                namespace: '${ $.ns }'
            },
            renderSettings: {
                url: '${ $.render_url }',
                dataType: 'html'
            },
            updateSettings: {
                url: '${ $.update_url }',
                dataType: 'json'
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

            _.bindAll(this, 'onRender', 'onUpdate');

            $.async('.' + this.contentSelector, function (el) {
                self.contentEl = $(el);

                if (self.autoRender) {
                    self.render();
                }
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
            this.initDataLink(config)._super();
            this.contentSelector = this.contentSelector.replace(/\./g, '_');

            return this;
        },

        /**
         * Sync data with external provider.
         *
         * @param {Object} config
         * @returns {Object}
         */
        initDataLink: function (config) {
            var key, value;

            if (config.dataLinks) {
                _.each(config.externalData, function (val) {
                    value = val;
                    key = 'externalValue.' + val.replace('data.', '');

                    if (config.dataLinks.imports) {
                        this.imports[key] = '${ $.externalProvider }:' + value;
                    }

                    if (config.dataLinks.exports) {
                        this.exports[key] = '${ $.externalProvider }:' + value;
                    }
                    this.links[key] = '${ $.externalProvider }:' + value;
                }, this.constructor.defaults);
            }

            if (config.realTimeLink) {
                this.constructor.defaults.links.externalValue = 'value';
            }

            return this;
        },

        /**
         * Request for render content.
         *
         * @returns {Object|Boolean}
         */
        render: function () {
            var request;

            if (this.isRendered) {
                return false;
            }

            this.startRender = true;
            request = this.requestData(this.params, this.renderSettings);
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
            this.isRendered = true;
            this.startRender = false;
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
        },

        /**
         * Request for update data.
         *
         * @returns {*|Object}
         */
        updateData: function (params) {
            var request;

            _.extend(params, this.params);

            if (!this.startRender && !this.isRendered) {
                return this.render();
            }

            request = this.requestData(params, this.updateSettings);
            request
                .done(this.onUpdate)
                .fail(this.onError);

            return request;
        },

        /**
         * Set data to external provider, clear changes.
         *
         * @param {*} data
         */
        onUpdate: function (data) {
            this.externalSource().set('data', data);
            this.externalSource().trigger('data.overload');
            this.loading(false);
        }
    });
});
