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
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter'
    ],
    function (Component, ko, quote, stepNavigator, paymentService, methodConverter) {
        'use strict';

        /** Set payment methods to collection */
        paymentService.setPaymentMethods(methodConverter(window.checkoutConfig.paymentMethods));

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/payment',
                activeMethod: ''
            },
            isVisible: ko.observable(quote.isVirtual()),
            quoteIsVirtual: quote.isVirtual(),
            isPaymentMethodsAvailable: ko.computed(function () {
                return paymentService.getAvailablePaymentMethods().length > 0;
            }),

            initialize: function () {
                this._super();
                stepNavigator.registerStep('billing', 'Review & Payments', this.isVisible, 20);
                return this;
            },

            getFormKey: function() {
                return window.checkoutConfig.formKey;
            }
        });
    }
);
