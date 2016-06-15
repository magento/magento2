/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
        'jquery',
        'Magento_Customer/js/model/authentication-popup',
        'Magento_Customer/js/customer-data'
    ],
    function ($, authenticationPopup, customerData) {
        'use strict';

        return function (config, element) {
            $(element).click(function (event) {
                var cart = customerData.get('cart'),
                    customer = customerData.get('customer');

                event.preventDefault();

                if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                    authenticationPopup.showModal();

                    return false;
                }
                location.href = config.checkoutUrl;
            });

        };
    }
);
