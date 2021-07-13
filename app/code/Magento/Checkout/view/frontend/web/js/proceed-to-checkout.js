/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Customer/js/model/authentication-popup',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote'
], function ($, authenticationPopup, customerData, quote) {
    'use strict';

    return function (config, element) {
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

        quote.totals.subscribe(function (totals) {
            if (totals['is_minimum_order_amount']) {
                $(element).prop('disabled', false);
                $(element).removeClass('disabled');

                return;
            }

            $(element).prop('disabled', true);
            $(element).addClass('disabled');
        });
    };
});
