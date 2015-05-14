/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/method-info'
    ],
    function (methodInfo) {
        return methodInfo.extend({
            getInstructions: function() {
                return window.checkoutConfig.payment.instructions[this.getCode()];
            },
            getInfo: function() {
                var info = [];
                if (this.getInstructions()) {
                    info.push({html: this.getInstructions()});
                }
                return info;
            }
        });
    }
);
