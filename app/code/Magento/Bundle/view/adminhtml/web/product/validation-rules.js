/**
 * Bundle client side validation rules
 *
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
define(["jquery","mage/backend/validation"], function($){
    $.validator.addMethod('validate-greater-zero-based-on-option', function(v, el) {
        var optionType = $(el)
                .closest('.form-list')
                .prev('.fieldset-alt')
                .find('select.select-product-option-type'),
            optionTypeVal = optionType.val();
        v = Number(v) || 0;
        if (optionType && (optionTypeVal == 'checkbox' || optionTypeVal == 'multi') && v <= 0) {
            return false;
        }
        return true;
    }, 'Please enter a number greater 0 in this field.');
});