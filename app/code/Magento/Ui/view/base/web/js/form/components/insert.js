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
            params: {
                namespace: '${ $.ns }'
            },
            renderSettings: {
                url: '${ $.render_url }',
                dataType: 'html'
            },
            externalLinks: {
                imports: {
                    updateUrl: '${ $.externalProvider }:update_url'
                }
            },
            links: {
                value: '${ $.provider }:${ $.dataScope}'
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
        initConfig: function () {
            this._super();
            this.contentSelector = this.contentSelector.replace(/\./g, '_').substr(1);

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
            this.initExternalLinks();
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
         * Initialize links to external provider.
         *
         * @returns {Object}
         */
        initExternalLinks: function () {
            this.setListeners(this.externalLinks.listens)
                .setLinks(this.externalLinks.links, 'imports')
                .setLinks(this.externalLinks.links, 'exports');

            _.each({
                exports: this.externalLinks.exports,
                imports: this.externalLinks.imports
            }, this.setLinks, this);

            return this;
        }
    });
});
