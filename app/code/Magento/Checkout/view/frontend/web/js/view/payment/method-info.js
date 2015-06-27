/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/generic',
        '../../model/payment-service'
    ],
    function (generic, paymentService) {
        return generic.extend({
            defaults: {
                titleTemplate: 'Magento_Checkout/payment/generic-title',
                displayArea: 'paymentMethods',
                isEnabled: true
            },
            initObservable: function () {
                this._super()
                    .observe('isEnabled');
                return this;
            },
            getMethod: function() {
                var paymentMethods = _.indexBy(paymentService.getAvailablePaymentMethods(), 'code');

                return paymentMethods[this.getCode()];
            },
            isAvailable: function() {
                return this.getMethod() != null;
            },
            getTitle: function() {
                return this.isAvailable() ? this.getMethod()['title'] : '';
            },
            isActive: function(parent) {
                return this.isAvailable() && parent.isMethodActive(this.getCode());
            }
        });
    }
);
