/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/fieldset'
], function (Fieldset) {
    'use strict';

    return Fieldset.extend({
        defaults: {
            imports: {
                onStockChange: '${ $.provider }:data.product.stock_data.manage_stock'
            }
        },

        /**
         * Disable all child elements if manage stock is zero
         * @param {Integer} canManageStock
         */
        onStockChange: function (canManageStock) {
            if (canManageStock === 0) {
                this.delegate('disabled', true);
            }
        }
    });
});
