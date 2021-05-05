/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    return function (config) {
        var dataForm = $('#' + config.formId);

        dataForm.submit(function () {
            $(this).find(':submit').attr('disabled', 'disabled');

            if (this.isValid === false) {
                $(this).find(':submit').prop('disabled', false);
            }
            this.isValid = true;
        });
        dataForm.bind('invalid-form.validate', function () {
            $(this).find(':submit').prop('disabled', false);
            this.isValid = false;
        });
    };
});
