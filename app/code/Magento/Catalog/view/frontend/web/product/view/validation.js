/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            "jquery",
            "jquery/ui",
            "mage/validation"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    "use strict";
    
    $.widget("mage.validation", $.mage.validation, {
        options: {
            radioCheckboxClosest: 'ul',
            errorPlacement: function (error, element) {
                if (element.attr('data-validate-message-box')) {
                    var messageBox = $(element.attr('data-validate-message-box'));
                    messageBox.html(error);
                    return;
                }
                var dataValidate = element.attr('data-validate');
                if (dataValidate && dataValidate.indexOf('validate-one-checkbox-required-by-name') > 0) {
                    error.appendTo('#links-advice-container');
                } else if (element.is(':radio, :checkbox')) {
                    element.closest(this.radioCheckboxClosest).after(error);
                } else {
                    element.after(error);
                }
            },
            highlight: function (element, errorClass) {
                var dataValidate = $(element).attr('data-validate');
                if (dataValidate && dataValidate.indexOf('validate-required-datetime') > 0) {
                    $(element).parent().find('.datetime-picker').each(function() {
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
}));