/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['jquery'], function ($) {

    var when = $.when($.get('braintree/paypal/getbuttondata', {isAjax: true}));

    return {
        when: function () {
            return when;
        },

        request: function () {
            return $.get('braintree/paypal/getbuttondata', {isAjax: true});
        }
    };
});
