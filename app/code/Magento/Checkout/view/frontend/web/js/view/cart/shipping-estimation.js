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
        'Magento_Checkout/js/model/shipping-rate-service',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'uiRegistry',
        'Magento_Checkout/js/model/quote',
        'mage/validation'
    ],
    function(
        $,
        Component,
        selectShippingAddress,
        addressConverter,
        shippingRateService,
        checkoutData,
        shippingRatesValidator,
        registry,
        quote,
        registry
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
                var self = this;
                this._super();
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();
                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend({}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                        self.getEstimationInfo();
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
                    shippingRatesValidator.bindChangeHandlers(element.elems());
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
