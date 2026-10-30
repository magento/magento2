/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data'
], function ($, authenticationPopup, customerData) {
    'use strict';

    return function (config, element) {
        $(window).on('pageshow', function (event) {
            if (event.originalEvent.persisted) {
                $(element).attr('disabled', false);
            }
        });

        $(element).on('click', function (event) {
            var cart = customerData.get('cart'),
                customer = customerData.get('customer');

            event.preventDefault();

            if (!customer().firstname && cart().isGuestCheckoutAllowed === false) {
                authenticationPopup.showModal();

                return false;
            }
            $(element).attr('disabled', true);
            location.href = config.checkoutUrl;
        });

    };
});
