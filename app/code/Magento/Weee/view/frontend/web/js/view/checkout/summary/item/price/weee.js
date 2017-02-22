/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component,quote) {
        "use strict";
        return Component.extend({

            isDisplayPriceWithWeeeDetails: function(item) {
                if(!parseFloat(item.weee_tax_applied_amount) || parseFloat(item.weee_tax_applied_amount <= 0)) {
                    return false;
                }
                return window.checkoutConfig.isDisplayPriceWithWeeeDetails;
            },
            isDisplayFinalPrice: function(item) {
                if(!parseFloat(item.weee_tax_applied_amount)) {
                    return false;
                }
                return window.checkoutConfig.isDisplayFinalPrice;
            },
            getWeeeTaxApplied: function(item) {
                if (item.weee_tax_applied) {
                    return JSON.parse(item.weee_tax_applied)
                }
                return [];

            }
        });
    }
);
