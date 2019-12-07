/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery-ui-modules/widget',
    'mage/validation/validation'
], function ($) {
    'use strict';

    $.widget('mage.validation', $.mage.validation, {
        options: {
            radioCheckboxClosest: 'ul, ol',

            /**
             * @param {*} error
             * @param {HTMLElement} element
             */
            errorPlacement: function (error, element) {
                var messageBox,
                    dataValidate;

                if ($(element).hasClass('datetime-picker')) {
                    element = $(element).parent();

                    if (element.parent().find('[generated=true].mage-error').length) {
                        return;
                    }
                }

                if (element.attr('data-errors-message-box')) {
                    messageBox = $(element.attr('data-errors-message-box'));
                    messageBox.html(error);

                    return;
                }

                dataValidate = element.attr('data-validate');

                if (dataValidate && dataValidate.indexOf('validate-one-checkbox-required-by-name') > 0) {
                    error.appendTo('#links-advice-container');
                } else if (element.is(':radio, :checkbox')) {
                    element.closest(this.radioCheckboxClosest).after(error);
                } else {
                    element.after(error);
                }
            },

            /**
             * @param {HTMLElement} element
             * @param {String} errorClass
             */
            highlight: function (element, errorClass) {
                var dataValidate = $(element).attr('data-validate');

                if (dataValidate && dataValidate.indexOf('validate-required-datetime') > 0) {
                    $(element).parent().find('.datetime-picker').each(function () {
                        $(this).removeClass(errorClass);

                        if ($(this).val().length === 0) {
                            $(this).addClass(errorClass);
                        }
                    });
                } else if ($(element).is(':radio, :checkbox')) {
                    $(element).closest(this.radioCheckboxClosest).addClass(errorClass);
                } else {
                    $(element).addClass(errorClass);
                }
            },

            /**
             * @param {HTMLElement} element
             * @param {String} errorClass
             */
            unhighlight: function (element, errorClass) {
                var dataValidate = $(element).attr('data-validate');

                if (dataValidate && dataValidate.indexOf('validate-required-datetime') > 0) {
                    $(element).parent().find('.datetime-picker').removeClass(errorClass);
                } else if ($(element).is(':radio, :checkbox')) {
                    $(element).closest(this.radioCheckboxClosest).removeClass(errorClass);
                } else {
                    $(element).removeClass(errorClass);
                }
            }
        }
    });

    return $.mage.validation;
});
