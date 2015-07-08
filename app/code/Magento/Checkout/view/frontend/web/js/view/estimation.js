/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, totals) {
        'use strict';
        return Component.extend({
            getQuantity: function() {
                if (totals.totals()) {
                    return parseFloat(totals.totals().items_qty);
                }
                return 0;
            },
            getPureValue: function() {
                if (totals.totals()) {
                    return parseFloat(totals.getSegment('grand_total').value);
                }
                return 0;
            },
            getValue: function () {
                return this.getFormattedPrice(this.getPureValue());
            }

        });
    }
);

