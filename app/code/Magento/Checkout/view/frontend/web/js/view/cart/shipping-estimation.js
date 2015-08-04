/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'Magento_Ui/js/form/form',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/shipping-rate-service',
        'Magento_Checkout/js/model/shipping-service',
        'mage/validation'
    ],
    function(
        Component,
        selectShippingAddress,
        addressConverter,
        shippingRateService,
        shippingService
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/cart/shipping-estimation'
            },
            isLoading: shippingService.isLoading,

            getEstimationInfo: function () {
                var addressData = null;
                this.source.set('params.invalid', false);
                this.source.trigger('shippingAddress.data.validate');

                if (!this.source.get('params.invalid')) {
                    addressData = this.source.get('shippingAddress');
                    console.log(addressData);
                    selectShippingAddress(addressConverter.formAddressDataToQuoteAddress(addressData));
                }
            }
        });
    }
);
