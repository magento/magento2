/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    return function (data, element) {

        if (modal.modal) {
            modal.modal.html($(element).html());
        } else {
            modal.modal = $(element).modal({
                modalClass: data.class,
                type: 'popup',
                buttons: []
            });
        }

        modal.modal.modal('openModal');
    };
});
