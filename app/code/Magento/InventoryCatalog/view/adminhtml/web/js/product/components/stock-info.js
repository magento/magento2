/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'Magento_Ui/js/form/element/abstract'
], function (_, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            template: 'Magento_InventoryCatalog/product/stock-info'
        },

        getStocksQtyInfo: function (dataSource) {
            var result,
                stocks = dataSource.stocks;

            result = stocks ? stocks : [];
            return result;
        }
    });
});
