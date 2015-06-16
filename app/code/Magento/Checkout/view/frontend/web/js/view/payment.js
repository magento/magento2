/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'uiComponent',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/payment-service'
    ],
    function (Component, ko, quote, stepNavigator, paymentService) {

        /** Set payment methods to collection */
        paymentService.setPaymentMethods(window.checkoutConfig.paymentMethods);

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/payment',
                activeMethod: ''
            },
            isVisible: ko.observable(quote.isVirtual()),
            quoteIsVirtual: quote.isVirtual(),

            initialize: function () {
                this._super();
                stepNavigator.registerStep('billing', 'Review & Payments', this.isVisible, 20);
                return this;
            }
        })
    }
);
