/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function ($, confirm) {
    'use strict';

    /**
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

    $(document).on('click', '#order-view-cancel-button', function () {
        var msg = $.mage.__('Are you sure you want to cancel this order?'),
            url = $('#order-view-cancel-button').data('url');

        confirm({
            'content': msg,
            'actions': {

                /**
                 * 'Confirm' action handler.
                 */
                confirm: function () {
                    getForm(url).appendTo('body').trigger('submit');
                }
            }
        });

        return false;
    });

    $(document).on('click', '#order-view-hold-button', function () {
        var url = $('#order-view-hold-button').data('url');

        getForm(url).appendTo('body').trigger('submit');
    });

    $(document).on('click', '#order-view-unhold-button', function () {
        var url = $('#order-view-unhold-button').data('url');

        getForm(url).appendTo('body').trigger('submit');
    });
});
