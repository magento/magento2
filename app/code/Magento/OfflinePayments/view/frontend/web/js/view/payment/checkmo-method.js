/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/method-info',
        'mage/translate'
    ],
    function (methodInfo, $t) {
        return methodInfo.extend({
            getMailingAddress: function() {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            getPayableTo: function() {
                return window.checkoutConfig.payment.checkmo.payableTo;
            },
            getInfo: function() {
                var info = [];
                if (this.getPayableTo()) {
                    info.push({name: $t('Make Check payable to')});
                    info.push({value: this.getPayableTo()});
                }
                if (this.getMailingAddress()) {
                    info.push({name: $t('Send Check to')});
                    info.push({html: this.getMailingAddress()});
                }
                return info;
            }
        });
    }
);
