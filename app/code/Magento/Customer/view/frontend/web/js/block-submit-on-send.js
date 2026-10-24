/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    return function (config) {
        var dataForm = $('#' + config.formId);

        dataForm.on('submit', function () {
            $('#' + this.id + ' div.mage-error').remove();
            $(this).find(':submit').attr('disabled', 'disabled');

            if (this.isValid === false) {
                $(this).find(':submit').prop('disabled', false);
            }
            this.isValid = true;
        });
        dataForm.on('invalid-form.validate', function () {
            $(this).find(':submit').prop('disabled', false);
            this.isValid = false;
        });
    };
});
