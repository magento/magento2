/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'Magento_Ui/js/form/form',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/cart/estimate-service',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'uiRegistry',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'mage/validation'
    ],
    function(
        $,
        Component,
        selectShippingAddress,
        addressConverter,
        estimateService,
        checkoutData,
        shippingRatesValidator,
        registry,
        quote,
        checkoutDataResolver
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/cart/shipping-estimation'
            },
            isVirtual: quote.isVirtual(),

            /**
             * @override
             */
            initialize: function () {
                this._super();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    checkoutDataResolver.resolveEstimationAddress();
                    var shippingAddress = quote.shippingAddress();
                    if (shippingAddress) {
                        var shippingAddressData = addressConverter.quoteAddressToFormAddressData(shippingAddress);
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend({}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                    checkoutProvider.on('shippingAddress', function (shippingAddressData) {
                        checkoutData.setShippingAddressFromData(shippingAddressData);
                    });
                });
            },

            /**
             * @override
             */
            initElement: function(element) {
                if (element.index === 'address-fieldsets') {
                    shippingRatesValidator.bindChangeHandlers(element.elems(), true, 500);
                }
            },

            /**
             * Returns shipping rates for address
             * @returns void
             */
            getEstimationInfo: function () {
                var addressData = null;
                this.source.set('params.invalid', false);
                this.source.trigger('shippingAddress.data.validate');

                if (!this.source.get('params.invalid')) {
                    addressData = this.source.get('shippingAddress');
                    selectShippingAddress(addressConverter.formAddressDataToQuoteAddress(addressData));
                }
            }
        });
    }
);
