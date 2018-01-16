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
            visible: true,
            label: '',
            showLabel: true,
            required: false,
            template: 'Magento_InventoryCatalog/product/stock-info',
            //template: 'ui/group/group',
            //fieldTemplate: 'ui/form/field',
            //fieldTemplate: 'Magento_InventoryCatalog/product/stock-info-field',
            breakLine: true,
            validateWholeGroup: false,
            additionalClasses: {}
        },

        getStocksQtyInfo: function (dataSource) {
            var $this = this;
            //var result = dataSource[this.index] ? dataSource[this.index] : [];
            var result = dataSource.stocks;

            return result;
        }


    });
});
