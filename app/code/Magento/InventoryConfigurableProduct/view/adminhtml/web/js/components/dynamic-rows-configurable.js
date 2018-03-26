/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_ConfigurableProduct/js/components/dynamic-rows-configurable'
], function (dynamicRowsConfigurable) {
    'use strict';

    return dynamicRowsConfigurable.extend({
        /** @inheritdoc */
        getProductData: function (row) {
            var product = this._super(row);

            product['quantity_per_source'] = row.quantityPerSource;

            return product;
        }
    });
});
