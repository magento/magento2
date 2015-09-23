/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    "jquery",
    "mage/translate"
], function ($) {
    'use strict';

    $('#order-view-cancel-button').click(function () {
        var msg = $.mage.__('Are you sure you want to cancel this order?');
        var url = $('#order-view-cancel-button').data('url');

        if (confirm(msg)) {
            getForm(url).submit();
        } else {
            return false;
        }
    });

    $('#order-view-hold-button').click(function () {
        var url = $('#order-view-hold-button').data('url');
        getForm(url).submit();
    });

    $('#order-view-unhold-button').click(function () {
        var url = $('#order-view-unhold-button').data('url');
        getForm(url).submit();
    });

    function getForm(url) {
        return $('<form>', {
            'action': url,
            'method': 'POST'
        }).append($('<input>', {
            'name': 'form_key',
            'value': FORM_KEY,
            'type': 'hidden'
        }));
    }
});
