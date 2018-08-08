/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
*/

define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($) {
    'use strict';

    return function (data, element) {
        if (this.modal) {
            this.modal.html($(element).html());
        } else {
            this.modal = $(element).modal({
                modalClass: data.class,
                type: 'popup',
                buttons: []
            });
        }
        this.modal.modal('openModal');
    };
});
