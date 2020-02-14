/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'jquery',
    'mageUtils',
    'uiElement',
    'Magento_Catalog/js/product/storage/storage-service',
    'Magento_Customer/js/customer-data',
    'Magento_Catalog/js/product/view/product-ids-resolver'
], function (_, $, utils, Element, storage, customerData, productResolver) {
    'use strict';

    return Element.extend({
        defaults: {
            identifiersConfig: {
                namespace: ''
            },
            productStorageConfig: {
                namespace: 'product_data_storage',
                customerDataProvider: 'product_data_storage',
                updateRequestConfig: {
                    url: '',
                    method: 'GET',
                    dataType: 'json'
                },
                className: 'DataStorage'
            },
            ids: {},
            listens: {
                ids: 'idsHandler'
            }
        },

        /**
         * Initializes provider component.
         *
         * @returns {Provider} Chainable.
         */
        initialize: function () {
            this._super()
                .initIdsStorage();

            return this;
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super();
            this.observe('ids');

            return this;
        },

        /**
         * Initializes ids storage.
         *
         * @returns {Provider} Chainable.
         */
        initIdsStorage: function () {
            storage.onStorageInit(this.identifiersConfig.namespace, this.idsStorageHandler.bind(this));

            return this;
        },

        /**
         * Initializes ids storage handler.
         *
         * @param {Object} idsStorage
         */
        idsStorageHandler: function (idsStorage) {
            this.idsStorage = idsStorage;
            this.productStorage = storage.createStorage(this.productStorageConfig);
            this.productStorage.data.subscribe(this.dataCollectionHandler.bind(this));

            if (~~this.idsStorage.allowToSendRequest) {
                customerData.reload([idsStorage.namespace]).done(this._resolveDataByIds.bind(this));
            } else {
                this._resolveDataByIds();
            }
        },

        /**
         * Callback, which load by ids from ids-storage product data
         *
         * @private
         */
        _resolveDataByIds: function () {
            this.initIdsListener();
            this.idsMerger(
                this.idsStorage.get(),
                this.prepareDataFromCustomerData(customerData.get(this.identifiersConfig.namespace)())
            );

            if (!_.isEmpty(this.productStorage.data())) {
                this.dataCollectionHandler(this.productStorage.data());
            } else {
                this.productStorage.setIds(this.data.currency, this.data.store, this.ids());
            }
        },

        /**
         * Init ids storage listener.
         */
        initIdsListener: function () {
            customerData.get(this.identifiersConfig.namespace).subscribe(function (data) {
                this.idsMerger(this.prepareDataFromCustomerData(data));
            }.bind(this));
            this.idsStorage.data.subscribe(this.idsMerger.bind(this));
        },

        /**
         * Prepare data from customerData.
         *
         * @param {Object} data
         *
         * @returns {Object}
         */
        prepareDataFromCustomerData: function (data) {
            data = data.items ? data.items : data;

            return data;
        },

        /**
         * Filter ids by their lifetime in order to show only hot ids :)
         *
         * @param {Object} ids
         * @returns {Array}
         */
        filterIds: function (ids) {
            var _ids = {},
                currentTime = new Date().getTime() / 1000,
                currentProductIds = productResolver($('#product_addtocart_form')),
                productCurrentScope = this.data.productCurrentScope,
                scopeId = productCurrentScope === 'store' ? window.checkout.storeId :
                productCurrentScope === 'group' ? window.checkout.storeGroupId :
                    window.checkout.websiteId;

            _.each(ids, function (id, key) {
                if (
                    currentTime - ids[key]['added_at'] < ~~this.idsStorage.lifetime &&
                    !_.contains(currentProductIds, ids[key]['product_id']) &&
                    (!id.hasOwnProperty('scope_id') || ids[key]['scope_id'] === scopeId)
                ) {
                    _ids[id['product_id']] = id;

                }
            }, this);

            return _ids;
        },

        /**
         * Merges id from storage and customer data
         *
         * @param {Object} data
         * @param {Object} optionalData
         */
        idsMerger: function (data, optionalData) {
            if (data && optionalData) {
                data = _.extend(data, optionalData);
            }

            if (!_.isEmpty(data)) {
                this.ids(
                    this.filterIds(_.extend(this.ids(), data))
                );
            }
        },

        /**
         * Ids update handler
         *
         * @param {Object} data
         */
        idsHandler: function (data) {
            this.productStorage.setIds(this.data.currency, this.data.store, data);
        },

        /**
         * Process data
         *
         * @param {Object} data
         */
        processData: function (data) {
            var curData = utils.copy(this.data),
                ids = this.ids();

            delete data['data_id'];
            data = _.values(data);

            _.each(data, function (record, index) {
                record._rowIndex = index;
                record['added_at'] = ids[record.id]['added_at'];
            }, this);

            curData.items = data;
            this.set('data', curData);
        },

        /**
         * Product storage data handler
         *
         * @param {Object} data
         */
        dataCollectionHandler: function (data) {
            data = this.filterData(data);
            this.processData(data);
        },

        /**
         * Filters data from product storage by ids
         *
         * @param {Object} data
         *
         * @returns {Object}
         */
        filterData: function (data) {
            var result = {},
                i = 0,
                ids = _.keys(this.ids()),
                length = ids.length;

            for (i; i < length; i++) {
                if (ids[i] && data[ids[i]]) {
                    result[ids[i]] = data[ids[i]];
                }
            }

            return result;
        }
    });
});
