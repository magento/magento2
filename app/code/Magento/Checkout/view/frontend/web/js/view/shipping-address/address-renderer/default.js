/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/action/select-shipping-address'
], function(ko, Component, selectShippingAddressAction) {
    'use strict';
    var countryData = window.checkoutConfig.countryData;
    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-address/address-renderer/default',
            isSelected: ko.observable(false)
        },

        getCountryName: function(countryId) {
            return (countryData[countryId] != undefined) ? countryData[countryId].name : "";
        },

        /** Set selected customer shipping address  */
        selectAddress: function() {
            selectShippingAddressAction(this.address);
            // Notify parent that this tile is selected
            this.containers[0].selectAddressTile(this.index);
        }
    });
});
