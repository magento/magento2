/**
 * Bundle client side validation rules
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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