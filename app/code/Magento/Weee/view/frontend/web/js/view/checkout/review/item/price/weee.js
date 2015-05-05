/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils'
    ],
    function (Component,quote, priceUtils) {
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
            getFormattedPrice: function (price) {
                //todo add format data
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            }
        });
    }
);
