define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';
    var enhancedMageValidation = {
        /**
         * @param {*} error
         * @param {*} element
         */
        options: {
            errorPlacement: function (error, element) {
                var errorPlacement = element,
                    fieldWrapper,messageBox;

                // logic for date-picker error placement
                if (element.hasClass('_has-datepicker')) {
                    errorPlacement = element.siblings('button');
                }
                // logic for field wrapper
                fieldWrapper = element.closest('.addon');

                if (fieldWrapper.length) {
                    errorPlacement = fieldWrapper.after(error);
                }
                //logic for checkboxes/radio
                if (element.is(':checkbox') || element.is(':radio')) {
                    errorPlacement = element.parents('.control').children().last();

                    //fallback if group does not have .control parent
                    if (!errorPlacement.length) {
                        errorPlacement = element.siblings('label').last();
                    }
                }
                if (element.attr('data-errors-msg-box')) {
                    messageBox = $(element.attr('data-errors-msg-box'));
                    messageBox.html(error);
                    return;
                }
                //logic for control with tooltip
                if (element.siblings('.tooltip').length) {
                    errorPlacement = element.siblings('.tooltip');
                }
                //logic for select with tooltip in after element
                if (element.next().find('.tooltip').length) {
                    errorPlacement = element.next();
                }
                errorPlacement.after(error);
            }

        }
    };

    return function (mageValidation) {
        $.widget('mage.validation', mageValidation, enhancedMageValidation);
        return $.mage.validation;
    };
});
