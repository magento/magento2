/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'ko',
        'uiComponent',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/payment-service'
    ],
    function (ko, Component, navigator, quote, shippingService, paymentService) {
        var className = ko.observable();
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/progress'
            },
            getClassName: function()
            {
                className('opc-block-progress');
                if(quote.getBillingAddress()() && (quote.getShippingAddress()()) || quote.isVirtual()) {
                    className('opc-block-progress active')
                }
                if (quote.getPaymentMethod()()) {
                    className('opc-block-progress order-review-step')
                }
                return className()
            },

            isShowStep: function (stepName) {
                switch(stepName){
                    case 'shippingAddress':
                        if (quote.isVirtual()) {
                            return false
                        }
                        return navigator.findStepByName(stepName).isEnabled;
                        break;
                    case 'shippingMethod':
                        if (quote.isVirtual()) {
                            return false
                        }
                        return navigator.findStepByName(stepName).isEnabled;
                        break;
                    default:
                        return navigator.findStepByName(stepName).isEnabled;
                }
            },
            isStepComplete: function(stepName) {
                switch(stepName){
                    case 'billingAddress':
                        return quote.getFormattedBillingAddress()|| false;
                    break;
                    case 'shippingAddress':
                        return quote.getFormattedShippingAddress()||false;
                    break;
                    case 'shippingMethod':
                        return quote.getShippingMethod()||false;
                    break;
                    case 'paymentMethod':
                        return quote.getPaymentMethod()||false;
                        break;
                    default:
                        return false;
                }
            },
            getBillingAddress: function() {
                return quote.getFormattedBillingAddress()();
            },
            getShippingAddress: function() {
                return quote.getFormattedShippingAddress();
            },
            getShippingMethod: function() {
                return quote.getShippingMethod()
            },
            getPaymentMethod: function() {
                return quote.getPaymentMethod();
            },
            getPaymentMethodTitle: function() {
                var code = this.getPaymentMethod()();
                return paymentService.getTitleByCode(code)
            },
            getPaymentMethodInfo: function() {
                return paymentService.getSelectedPaymentInfo()
            },
            goToStep: function(stepName) {
                navigator.goToStep(stepName);
            },
            getShippingMethodTitle: function() {
                var code = this.getShippingMethod()();
                return shippingService.getTitleByCode(code)
            },
            getShippingRates: function() {
                var code = this.getShippingMethod()();
                return shippingService.getRateByCode(code)
            }
        });
    }
);
