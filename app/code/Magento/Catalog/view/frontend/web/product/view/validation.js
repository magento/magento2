/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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