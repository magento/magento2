/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'ko',
    'mageUtils',
    'Magento_Catalog/js/product/query-builder',
    'Magento_Customer/js/customer-data',
    'jquery/jquery-storageapi'
], function ($, _, ko, utils, queryBuilder, customerData) {
    'use strict';

    /**
     * Process data from API request
     *
     * @param {Object} data
     * @returns {Object}
     */
    function getParsedDataFromServer(data) {
        var result = {};

        _.each(data.items, function (item) {
                if (item.id) {
                    result[item.id] = item;
                }
            }
        );

        return {
            items: result
        };
    }

    /**
     * Set data to localStorage with support check.
     *
     * @param {String} namespace
     * @param {Object} data
     */
    function setLocalStorageItem(namespace, data) {
        try {
            window.localStorage.setItem(namespace, JSON.stringify(data));
        } catch (e) {
            console.warn('localStorage is unavailable - skipping local caching of product data');
            console.error(e);
        }
    }

    return {

        /**
         * Class name
         */
        name: 'DataStorage',
        request: {},
        customerDataProvider: 'product_data_storage',

        /**
         * Initialize class
         *
         * @return Chainable.
         */
        initialize: function () {
            if (!this.data) {
                this.data = ko.observable({});
            }

            this.initLocalStorage()
                .initCustomerDataReloadListener()
                .cachesDataFromLocalStorage()
                .initDataListener()
                .initProvideStorage()
                .initProviderListener();

            return this;
        },

        /**
         * Initialize listener to customer data reload
         *
         * @return Chainable.
         */
        initCustomerDataReloadListener: function () {
            $(document).on('customer-data-invalidate', this._flushProductStorage.bind(this));

            return this;
        },

        /**
         * Flush product storage
         *
         * @private
         * @return void
         */
        _flushProductStorage: function (event, sections) {
            if (_.isEmpty(sections) || _.contains(sections, 'product_data_storage')) {
                this.localStorage.removeAll();
            }
        },

        /**
         * Initialize listener to data property
         *
         * @return Chainable.
         */
        initDataListener: function () {
            this.data.subscribe(this.dataHandler.bind(this));

            return this;
        },

        /**
         * Initialize provider storage
         *
         * @return Chainable.
         */
        initProvideStorage: function () {
            this.providerHandler(customerData.get(this.customerDataProvider)());

            return this;
        },

        /**
         * Handler to update "data" property.
         * Sets data to localStorage
         *
         * @param {Object} data
         */
        dataHandler: function (data) {
            if (_.isEmpty(data)) {
                this.localStorage.removeAll();
            } else {
                setLocalStorageItem(this.namespace, data);
            }
        },

        /**
         * Handler to update data in provider.
         *
         * @param {Object} data
         */
        providerHandler: function (data) {
            var currentData = utils.copy(this.data()),
                ids = _.keys(data.items);

            if (data.items && ids.length) {
                //we can extend only items
                data = data.items;
                this.data(_.extend(currentData, data));
            }
        },

        /**
         * Sets data ids
         *
         * @param {String} currency
         * @param {String} store
         * @param {Object} ids
         */
        setIds: function (currency, store, ids) {
            if (!this.hasInCache(currency, store, ids)) {
                this.loadDataFromServer(currency, store, ids);
            } else {
                this.data.valueHasMutated();
            }
        },

        /**
         * Gets data from "data" property by identifiers
         *
         * @param {String} currency
         * @param {String} store
         * @param {Object} productIdentifiers
         *
         * @return {Object} data.
         */
        getDataByIdentifiers: function (currency, store, productIdentifiers) {
            var data = {},
                dataCollection = this.data(),
                id;

            for (id in productIdentifiers) {
                if (productIdentifiers.hasOwnProperty(id)) {
                    data[id] = dataCollection[id];
                }
            }

            return data;
        },

        /**
         * Checks has cached data or not
         *
         * @param {String} currency
         * @param {String} store
         * @param {Object} ids
         *
         * @return {Boolean}
         */
        hasInCache: function (currency, store, ids) {
            var data = this.data(),
                id;

            for (id in ids) {
                if (!data.hasOwnProperty(id) ||
                    data[id]['currency_code'] !== currency ||
                    ~~data[id]['store_id'] !== ~~store
                ) {
                    return false;
                }
            }

            return true;
        },

        /**
         * Load data from server by ids
         *
         * @param {String} currency
         * @param {String} store
         * @param {Object} ids
         *
         * @return void
         */
        loadDataFromServer: function (currency, store, ids) {
            var idsArray = _.keys(ids),
                prepareAjaxParams = {
                    'entity_id': idsArray.join(',')
                };

            if (this.request.sent && this.hasIdsInSentRequest(ids)) {
                return;
            }

            this.request = {
                sent: true,
                data: ids
            };

            this.updateRequestConfig.data = queryBuilder.buildQuery(prepareAjaxParams);
            this.updateRequestConfig.data['store_id'] = store;
            this.updateRequestConfig.data['currency_code'] = currency;
            $.ajax(this.updateRequestConfig).done(function (data) {
                this.request = {};
                this.providerHandler(getParsedDataFromServer(data));
            }.bind(this));
        },

        /**
         * Each product page consist product cache data,
         * this function prepare those data to appropriate view, and save it
         *
         * @param {Object} data
         */
        addDataFromPageCache: function (data) {
            this.providerHandler(getParsedDataFromServer(data));
        },

        /**
         * @param {Object} ids
         * @returns {Boolean}
         */
        hasIdsInSentRequest: function (ids) {
            var sentDataIds,
                currentDataIds;

            if (this.request.data) {
                sentDataIds = _.keys(this.request.data);
                currentDataIds = _.keys(ids);

                return _.every(currentDataIds, function (id) {
                    return _.lastIndexOf(sentDataIds, id) !== -1;
                });
            }

            return false;
        },

        /**
         * Initialize provider listener
         *
         * @return Chainable.
         */
        initProviderListener: function () {
            customerData.get(this.customerDataProvider).subscribe(this.providerHandler.bind(this));

            return this;
        },

        /**
         * Caches data from local storage to local scope
         *
         * @return Chainable.
         */
        cachesDataFromLocalStorage: function () {
            this.data(this.getDataFromLocalStorage());

            return this;
        },

        /**
         * Gets data from local storage by current namespace
         *
         * @return {Object}.
         */
        getDataFromLocalStorage: function () {
            return this.localStorage.get();
        },

        /**
         * Initialize localStorage
         *
         * @return Chainable.
         */
        initLocalStorage: function () {
            this.localStorage = $.initNamespaceStorage(this.namespace).localStorage;

            return this;
        }
    };
});
