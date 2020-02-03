/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    return function (config, element) {
        $(element)
            .mage('form')
            .mage('validation');

        /**
         * Toggle the field to use the default value
         *
         * @param {String} code
         * @param {String} value
         */
        function toggleUseDefault(code, value) {
            var checkbox = $('#custom_currency_symbol_inherit' + code),
                input = $('#custom_currency_symbol' + code);

            if (checkbox.is(':checked')) {
                input.addClass('disabled');
                input.val(value);
                input.prop('readonly', true);
            } else {
                input.removeClass('disabled');
                input.prop('readonly', false);
            }
        }

        window.toggleUseDefault = toggleUseDefault;
    };
});
