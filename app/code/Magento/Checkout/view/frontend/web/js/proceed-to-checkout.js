/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery',
        'Magento_Customer/js/model/authentication-popup',
        'Magento_Customer/js/customer-data'
    ],
    function($, authenticationPopup, customerData) {
        return function (config, element) {
            $(element).click(function(event) {
                event.preventDefault();
                var cart = customerData.get('cart'),
                    customer = customerData.get('customer');

                if (customer() == false && !cart().isGuestCheckoutAllowed) {
                    authenticationPopup.showModal();
                    return false;
                }
                location.href = window.authenticationPopup.checkoutUrl;
            });

        };
    }
);
