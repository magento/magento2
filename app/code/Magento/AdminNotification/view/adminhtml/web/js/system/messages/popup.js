/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict'; // eslint-disable-line strict

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
