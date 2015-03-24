/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'Magento_Ui/js/form/component',
        '../model/quote',
        '../model/payment-service',
        '../action/select-payment-method',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function ($, Component, quote, paymentService, selectPaymentMethod, navigator) {
        var paymentMethods = paymentService.getAvailablePaymentMethods(quote);
        var paymentMethodCount = paymentMethods.length;
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/payment',
                quoteHasShippingMethod: quote.getShippingMethod(),

                setPaymentMethod: function(form) {
                    var paymentMethodCode = $("input[name='payment[method]']:checked", form).val();
                    if (!paymentMethodCode) {
                        return;
                    }
                    selectPaymentMethod(paymentMethodCode, []);
                },
                getAvailablePaymentMethods: function() {
                    return paymentMethods;
                },
                getAvailablePaymentMethodCount: function() {
                    return paymentMethodCount;
                },
                isVisible: navigator.isStepVisible('paymentMethod'),
                backToShippingMethod: function() {
                    navigator.setCurrent('paymentMethod').goBack();
                }
            }
        });
    }
);
