/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
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
            visible: true,
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
            this._super();
            _.bindAll(this, 'onRender', 'onUpdate');

            if (this.autoRender) {
                this.render();
            }

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            return this._super()
                .observe([
                    'visible',
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
         * @returns {Object}
         */
        render: function (params) {
            var self = this,
                request;

            if (this.isRendered) {
                return this;
            }

            self.previousParams = params || {};

            $.async({
                component: this.name,
                ctx: '.' + this.contentSelector
            }, function (el) {
                self.contentEl = $(el);
                self.startRender = true;
                params = _.extend({}, self.params, params || {});
                request = self.requestData(params, self.renderSettings);
                request
                    .done(self.onRender)
                    .fail(self.onError);
            });

            return this;
        },

        /** @inheritdoc */
        destroy: function () {
            this.destroyInserted()
                ._super();
        },

        /**
         * Destroy inserted components.
         *
         * @returns {Object}
         */
        destroyInserted: function () {
            if (this.isRendered) {
                this.isRendered = false;
                this.content('');

                if (this.externalSource()) {
                    this.externalSource().destroy();
                }
                this.initExternalLinks();
            }

            return this;
        },

        /**
         * Initialize links on external components.
         *
         * @returns {Object}
         */
        initExternalLinks: function () {
            var imports = this.filterExternalLinks(this.imports, this.ns),
                exports = this.filterExternalLinks(this.exports, this.ns),
                links = this.filterExternalLinks(this.links, this.ns);

            this.setLinks(links, 'imports')
                .setLinks(links, 'exports');

            _.each({
                exports: exports,
                imports: imports
            }, this.setLinks, this);

            return this;
        },

        /**
         * Filter external links.
         *
         * @param {Object} data
         * @param {String }ns
         * @returns {Object}
         */
        filterExternalLinks: function (data, ns) {
            var links  = {};

            _.each(data, function (value, key) {
                if (value.split('.')[0] === ns) {
                    links[key] = value;
                }
            });

            return links;
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
                url: this['update_url'],
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

            params = _.extend(params || {}, this.params);

            if (!this.startRender && !this.isRendered) {
                return this.render(params);
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
