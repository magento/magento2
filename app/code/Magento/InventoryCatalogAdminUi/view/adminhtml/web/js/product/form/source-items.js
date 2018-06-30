/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/fieldset',
    'uiRegistry',
    'underscore'
], function (Fieldset, registry, _) {
    'use strict';

    return Fieldset.extend({
        defaults: {
            advancedInventoryButtonIndex: '',
            imports: {
                onStockChange: '${ $.provider }:data.product.stock_data.manage_stock'
            }
        },

        /**
         * "Advanced Inventory" button should stay active in any case.
         *
         * @param {Integer} canManageStock
         */
        onStockChange: function (canManageStock) {
            var advancedInventoryButton = registry.get('index = ' + this.advancedInventoryButtonIndex);

            if (canManageStock === 0) {
                if (!_.isUndefined(advancedInventoryButton)) {
                    advancedInventoryButton.disabled(false);
                }
            }
        }
    });
});
