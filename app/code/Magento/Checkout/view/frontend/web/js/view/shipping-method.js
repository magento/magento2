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
        '../action/select-shipping-method'
    ],
    function ($, Component, quote, shippingService, selectShippingMethod) {
        var loadedRates = shippingService.getAvailableShippingMethods(quote);

        return Component.extend({

            defaults: {
                template: 'Magento_Checkout/shipping-method',
                rates: function () {
                    return shippingService.sortRates(loadedRates)
                },

                isShippingRateGroupsAvailable: function () {
                    return loadedRates.length < 0
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
                    var shippingMethodCode = form.find("input[name='shipping_method'][checked]").val();
                    if (!shippingMethodCode) {
                        return;
                    }
                    selectShippingMethod(shippingMethodCode);
                }
            }

        });
    }
);
