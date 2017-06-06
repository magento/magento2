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
                .initIdsStorage();

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
         * Init ids storage handler
         *
         * @param {Object} idsStorage - storage instance
         */
        idsStorageHandler: function (idsStorage) {
            this.initProductStorage();
            this.idsStorage = idsStorage;
            this.idsStorage.add(this.getIdentifiers());
        },

        /**
         * Init product storage
         */
        initProductStorage: function () {
            this.productStorage = storage.createStorage(this.productStorageConfig);
            this.productStorage.providerHandler(this.data);
        },

        /**
         * Gets ids from items
         *
         * @returns {Object}
         */
        getIdentifiers: function () {
            var result = {};

            _.each(this.data.items, function (item, key) {
                result[key] = {
                    'added_at': new Date().getTime() / 1000,
                    'product_id': key
                };
            }, this);

            return result;
        }
    });
});
