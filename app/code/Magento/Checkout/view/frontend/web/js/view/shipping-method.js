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

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-method',
                rates: shippingService.getRates(),
                quoteHasShippingAddress: quote.getShippingAddress(),

                verifySelectedMethodCode: function (data, value) {
                    if (quote.getSelectedShippingMethod() == data) {
                        return value;
                    }
                    return false;
                },

                isErrorMessagePresent: function (data) {
                    return !data.available;
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
                isVisible: navigator.isStepVisible('shippingMethod'),
                // Checkout step navigation
                backToShippingAddress: function () {
                    navigator.setCurrent('shippingMethod').goBack();
                }
            }
        });
    }
);
