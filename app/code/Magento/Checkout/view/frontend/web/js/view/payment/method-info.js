/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        '../../model/payment-service'
    ],
    function (Component, paymentService) {
        return Component.extend({
            defaults: {
                titleTemplate: 'Magento_Checkout/payment/generic-title'
            },
            getCode: function() {
                return this.index;
            },
            getMethod: function() {
                var paymentMethods = _.indexBy(paymentService.getAvailablePaymentMethods()(), 'code');

                return paymentMethods[this.getCode()];
            },
            isAvailable: function() {
                return this.getMethod() != null;
            },
            getTitle: function() {
                return this.isAvailable() ? this.getMethod()['title'] : '';
            }
        });
    }
);
