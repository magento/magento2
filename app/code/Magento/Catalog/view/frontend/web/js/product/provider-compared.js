/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    './provider',
    'Magento_Catalog/js/product/storage/storage-service',
    'Magento_Customer/js/customer-data'
], function (_, Provider, storage, customerData) {
    'use strict';

    return Provider.extend({

        /**
         * Ids update handler
         *
         * @param {Object} data
         */
        idsHandler: function (data) {
            this.productStorage.setIds(this.data.currency, this.data.store, this.dataFilter(data));
        },

        /**
         * Filters data by provider
         *
         * @param {Object} data
         *
         * @returns {Object}
         */
        dataFilter: function (data) {
            var providerData = this.idsStorage.prepareData(customerData.get(this.identifiersConfig.provider)().items),
                result = {},
                productCurrentScope,
                scopeId;

            if (typeof this.data.productCurrentScope !== 'undefined') {
                productCurrentScope = this.data.productCurrentScope;
                scopeId = productCurrentScope === 'store' ? window.checkout.storeId :
                    productCurrentScope === 'group' ? window.checkout.storeGroupId :
                        window.checkout.websiteId;
                _.each(data, function (value, key) {
                    if (!providerData[productCurrentScope + '-' + scopeId + '-' + key]) {
                        result[key] = value;
                    }
                });
            } else {
                _.each(data, function (value, key) {
                    if (!providerData[key]) {
                        result[key] = value;
                    }
                });
            }

            return result;
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
                ids = _.keys(this.dataFilter(this.ids())),
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
