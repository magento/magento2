/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/fieldset',
    'uiRegistry'
], function (Fieldset, registry) {
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
            var advancedInventoryButton = registry.get('index = advanced_inventory_button');

            if (canManageStock === 0) {
                this.delegate('disabled', true);
                // "Advanced Inventory" button should stay active in any case.
                advancedInventoryButton.disabled(false);
            } else {
                this.delegate('disabled', false);
            }
        }
    });
});
