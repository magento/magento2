/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'Magento_Ui/js/form/components/fieldset'
], function (Fieldset) {
    'use strict';

    return Fieldset.extend({
        defaults: {
            additionalClasses: {},
            imports: {
                onStockChange: '${ $.provider }:data.product.stock_data.manage_stock'
            }
        },

        /**
         * Disable all child elements if manage stock is zero
         * @param manageStockValue
         */
        onStockChange: function(manageStockValue) {
            if (manageStockValue === 0) {
                this.delegate('disabled', true);
            }
        }

    });
});
