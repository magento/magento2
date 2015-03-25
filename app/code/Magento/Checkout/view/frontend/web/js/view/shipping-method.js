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
        '../model/shipping-service',
        '../action/select-shipping-method',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function ($, Component, quote, shippingService, selectShippingMethod, priceUtils, navigator) {
        var stepName = 'shippingMethod';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-method',
                stepNumber: function(){
                    return navigator.getStepNumber(stepName);
                },
                rates: shippingService.getRates(),
                // Checkout step navigation
                isVisible: navigator.isStepVisible(stepName),
            },
            quoteHasShippingAddress: function() {
                return quote.isVirtual() || quote.getShippingAddress();
            },

            verifySelectedMethodCode: function (data, value) {
                if (quote.getSelectedShippingMethod() == data) {
                    return value;
                }
                return false;
            },

            setShippingMethod: function (form) {
                form = $(form);
                var code = form.find("input[name='shipping_method']:checked").val();
                var shippingMethodCode = code ? code.split("_") : null;
                selectShippingMethod(shippingMethodCode);
            },

            getFormatedPrice: function (price) {
                //todo add format data
                return quote.getCurrencySymbol() + priceUtils.formatPrice(price)
            },


            isActive: function() {
                if (quote.isVirtual()) {
                    navigator.setStepEnabled(stepName, false);
                }
                return !quote.isVirtual();
            },
            backToShippingAddress: function () {
                navigator.setCurrent(stepName).goBack();
            }
        });
    }
);
