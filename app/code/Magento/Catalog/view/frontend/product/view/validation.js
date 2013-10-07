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
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
(function($) {
    $.widget("mage.validation", $.mage.validation, {
        options: {
            errorPlacement: function (error, element) {
                var dataValidate = element.attr('data-validate');
                if (dataValidate && dataValidate.indexOf('validate-one-checkbox-required-by-name') > 0) {
                    error.appendTo('#links-advice-container');
                } else if (dataValidate && dataValidate.indexOf('validate-grouped-qty') > 0) {
                    $('#super-product-table').siblings(this.errorElement + '.' + this.errorClass).remove();
                    $('#super-product-table').after(error);
                } else if (element.is(':radio, :checkbox')) {
                    element.closest('ul').after(error);
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
                    $(element).closest('ul').addClass(errorClass);
                } else {
                    $(element).addClass(errorClass);
                }
            },
            unhighlight: function (element, errorClass) {
                var dataValidate = $(element).attr('data-validate');
                if (dataValidate && dataValidate.indexOf('validate-required-datetime') > 0) {
                    $(element).parent().find('.datetime-picker').removeClass(errorClass);
                } else if ($(element).is(':radio, :checkbox')) {
                    $(element).closest('ul').removeClass(errorClass);
                } else {
                    $(element).removeClass(errorClass);
                }
            }
        }
    });
})(jQuery);