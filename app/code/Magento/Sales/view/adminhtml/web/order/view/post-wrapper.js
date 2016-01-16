/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    /**
     * Create and get form with form key
     * @param {String} url
     * @returns {jQuery}
     */
    function getForm(url) {
        return $('<form>', {
            'action': url,
            'method': 'POST'
        }).append($('<input>', {
            'name': 'form_key',
            'value': window.FORM_KEY,
            'type': 'hidden'
        }));
    }

    $('#order-view-cancel-button').click(function () {
        var msg = $.mage.__('Are you sure you want to cancel this order?'),
            url = $('#order-view-cancel-button').data('url');

        if (window.confirm(msg)) {
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
});
