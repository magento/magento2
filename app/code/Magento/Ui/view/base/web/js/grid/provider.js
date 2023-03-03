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
    'mageUtils',
    'rjsResolver',
    'uiLayout',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'uiElement',
    'uiRegistry',
    'Magento_Ui/js/grid/data-storage'
], function ($, _, utils, resolver, layout, alert, $t, Element, registry) {
    'use strict';

    return Element.extend({
        defaults: {
            firstLoad: true,
            lastError: false,
            storageConfig: {
                component: 'Magento_Ui/js/grid/data-storage',
                provider: '${ $.storageConfig.name }',
                name: '${ $.name }_storage',
                updateUrl: '${ $.update_url }'
            },
            listens: {
                params: 'onParamsChange',
                requestConfig: 'updateRequestConfig'
            },
            ignoreTmpls: {
                data: true
            },
            triggerDataReload: false
        },

        /**
         * Initializes provider component.
         *
         * @returns {Provider} Chainable.
         */
        initialize: function () {
            utils.limit(this, 'onParamsChange', 5);
            _.bindAll(this, 'onReload');

            this._super()
                .initStorage()
                .clearData();

            // Load data when there will
            // be no more pending assets.
            resolver(this.reload, this);

            return this;
        },

        /**
         * Initializes storage component.
         *
         * @returns {Provider} Chainable.
         */
        initStorage: function () {
            layout([this.storageConfig]);

            return this;
        },

        /**
         * Clears provider's data properties.
         *
         * @returns {Provider} Chainable.
         */
        clearData: function () {
            this.setData({
                items: [],
                totalRecords: 0,
                showTotalRecords: true
            });

            return this;
        },

        /**
         * Overrides current data with a provided one.
         *
         * @param {Object} data - New data object.
         * @returns {Provider} Chainable.
         */
        setData: function (data) {
            data = this.processData(data);

            this.set('data', data);

            return this;
        },

        /**
         * Processes data before applying it.
         *
         * @param {Object} data - Data to be processed.
         * @returns {Object}
         */
        processData: function (data) {
            var items = data.items;

            _.each(items, function (record, index) {
                record._rowIndex = index;
            });

            return data;
        },

        /**
         * Reloads data with current parameters.
         *
         * @returns {Promise} Reload promise object.
         */
        reload: function (options) {
            var request = this.storage().getData(this.params, options);

            this.trigger('reload');

            request
                .done(this.onReload)
                .fail(this.onError.bind(this));

            return request;
        },

        /**
         * Handles changes of 'params' object.
         */
        onParamsChange: function () {
            // It's necessary to make a reload only
            // after the initial loading has been made.
            if (!this.firstLoad) {
                this.reload();
            } else {
                this.triggerDataReload = true;
            }
        },

        /**
         * Handles reload error.
         */
        onError: function (xhr) {
            if (xhr.statusText === 'abort') {
                return;
            }

            this.set('lastError', true);

            this.firstLoad = false;
            this.triggerDataReload = false;

            alert({
                content: $t('Something went wrong.')
            });
        },

        /**
         * Handles successful data reload.
         *
         * @param {Object} data - Retrieved data object.
         */
        onReload: function (data) {
            this.firstLoad = false;
            this.set('lastError', false);
            this.setData(data)
                .trigger('reloaded');

            if (this.triggerDataReload) {
                this.triggerDataReload = false;
                this.reload();
            }
        },

        /**
         * Updates storage's request configuration
         *
         * @param {Object} requestConfig
         */
        updateRequestConfig: function (requestConfig) {
            registry.get(this.storageConfig.provider, function (storage) {
                _.extend(storage.requestConfig, requestConfig);
            });
        }
    });
});
