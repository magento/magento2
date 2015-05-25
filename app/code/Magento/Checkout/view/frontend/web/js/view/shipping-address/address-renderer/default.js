/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'uiComponent',
    'Magento_Checkout/js/action/select-shipping-address'
], function(Component, selectShippingAddressAction) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/shipping-address/address-renderer/default'
        },

        /** Set selected customer shipping address  */
        selectAddress: function() {
            selectShippingAddressAction(this.address)
        }
    });
});
