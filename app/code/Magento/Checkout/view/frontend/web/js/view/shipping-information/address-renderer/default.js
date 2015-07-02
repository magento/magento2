/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'uiComponent'
], function(Component) {
    'use strict';
    var countryData = window.checkoutConfig.countryData;
    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-information/address-renderer/default'
        },

        getCountryName: function(countryId) {
            return (countryData[countryId] != undefined) ? countryData[countryId].name : "";
        }
    });
});
