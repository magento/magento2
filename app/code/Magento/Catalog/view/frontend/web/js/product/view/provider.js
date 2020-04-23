/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiElement',
    'Magento_Catalog/js/product/storage/storage-service'
], function (_, Element, storage) {
    'use strict';

    return Element.extend({
        defaults: {
            identifiersConfig: {
                namespace: 'recently_viewed_product'
            },
            productStorageConfig: {
                namespace: 'product_data_storage',
                updateRequestConfig: {
                    method: 'GET',
                    dataType: 'json'
                },
                className: 'DataStorage'
            }
        },

        /**
         * Initializes
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            this._super()
                .initIdsStorage()
                .initDataStorage();

            return this;
        },

        /**
         * Init ids storage
         *
         * @returns {Object} Chainable.
         */
        initIdsStorage: function () {
            storage.onStorageInit(this.identifiersConfig.namespace, this.idsStorageHandler.bind(this));

            return this;
        },

        /**
         * Init data storage
         *
         * @returns {Object} Chainable.
         */
        initDataStorage: function () {
            storage.onStorageInit(this.productStorageConfig.namespace, this.dataStorageHandler.bind(this));

            return this;
        },

        /**
         * Init data storage handler
         *
         * @param {Object} dataStorage - storage instance
         */
        dataStorageHandler: function (dataStorage) {
            this.productStorage = dataStorage;
            this.productStorage.add(this.data.items);
        },

        /**
         * Init ids storage handler
         *
         * @param {Object} idsStorage - storage instance
         */
        idsStorageHandler: function (idsStorage) {
            this.idsStorage = idsStorage;
            this.idsStorage.add(this.getIdentifiers());
        },

        /**
         * Gets ids from items
         *
         * @returns {Object}
         */
        getIdentifiers: function () {
            var result = {},
                productCurrentScope = this.data.productCurrentScope,
                scopeId = productCurrentScope === 'store' ? window.checkout.storeId :
                    productCurrentScope === 'group' ? window.checkout.storeGroupId :
                        window.checkout.websiteId;

            _.each(this.data.items, function (item, key) {
                result[productCurrentScope + '-' + scopeId + '-' + key] = {
                    'added_at': new Date().getTime() / 1000,
                    'product_id': key,
                    'scope_id': scopeId
                };
            }, this);

            return result;
        }
    });
});
