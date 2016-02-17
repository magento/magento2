/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/select'
], function (Select) {
    'use strict';

    return Select.extend({
        defaults: {
            listens: {
                visible: 'changeVisibility',
                value: 'changeVisibility'
            },
            modules: {
                deferredStockUpdate: '${ $.deferredStockUpdate }'
            }
        },

        /**
         * Change visibility for deferredStockUpdate based on current visibility and value.
         */
        changeVisibility: function () {
            if (this.visible() && parseFloat(this.value()) && this.deferredStockUpdate) {
                this.deferredStockUpdate().visible(true);
            } else if (this.deferredStockUpdate) {
                this.deferredStockUpdate().visible(false);
            }
        }
    });
});
