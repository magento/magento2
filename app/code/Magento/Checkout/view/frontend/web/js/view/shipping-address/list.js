/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'uiComponent',
        'ko',
        'Magento_Checkout/js/action/select-shipping-address',
        '../../model/quote',
        '../../model/addresslist',
        'mage/translate'
    ],
    function(Component, ko, selectShippingAddressAction, quote, addressList) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping-address/list',
                visible: window.checkoutConfig.customerAddressCount
            },
            selectedShippingAddress: ko.computed(function(){
                if (!quote.getShippingAddress()()) {
                    quote.setShippingAddress(addressList.getDefaultShipping());
                }
                return quote.getShippingAddress()();
            }),

            /** Get all customer addresses  */
            addresses: function() {
                var addresses = addressList.getAddresses();
                addresses.forEach(function(entity){
                    entity.countryName = window.checkoutConfig.countryData[entity.countryId].name;
                });
                return addresses;
            },

            /** Set selected customer shipping address  */
            selectAddress: function(address) {
                selectShippingAddressAction(address)
            }
        });
    }
);
