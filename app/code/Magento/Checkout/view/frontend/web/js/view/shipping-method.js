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
        'Magento_Customer/js/model/customer',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function ($, Component, quote, shippingService, selectShippingMethod, customer, priceUtils, navigator) {
        var loadedRates = shippingService.getAvailableShippingMethods(quote);

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-method',
                isLoggedIn: customer.isLoggedIn(),
                quoteHasShippingAddress: quote.hasShippingAddress(),

                rates: function () {
                    return shippingService.sortRates(loadedRates)
                },

                isShippingRateGroupsAvailable: function () {
                    return loadedRates.length == 0
                },

                getRatesQty: function (data) {
                    return data.length && loadedRates.length == 1;
                },

                selectedMethodCode: shippingService.getSelectedShippingMethod(quote),

                verifySelectedMethodCode: function (data) {
                    return this.selectedMethodCode == data;
                },
                shippingCodePrice: function () {

                    return shippingService.getShippingCodePrice(loadedRates);
                },
                isErrorMessagePresent: function (data) {
                    return !data.available;
                },
                setShippingMethod: function (form) {
                    form = $(form);
                    var shippingMethodCode = form.find("input[name='shipping_method']:checked").val();
                    if (!shippingMethodCode) {
                        return;
                    }
                    selectShippingMethod(shippingMethodCode);
                },

                getFormatedPrice: function(price) {
                    //todo add format data
                    return quote.getCurrencySymbol() + priceUtils.formatPrice(price)
                },
                isVisible: navigator.isShippingMethodVisible(),
                // Checkout step navigation
                backToShippingAddress: function() {
                    navigator.toStep('shippingAddress');
                }
            }
        });
    }
);
