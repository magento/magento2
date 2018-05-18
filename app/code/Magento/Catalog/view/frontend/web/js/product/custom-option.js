/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';
    $.widget('mage.customOption', {

        _create : function(){
            this.characterCount();
        },

        /** @inheritDoc */
        characterCount : function() {
            var element = this.element,
            input = $('.product-custom-option', element),
            counter = $('.character-counter', element),
            count = $('.count', element),
            dataValidateString = input.attr('data-validate'),
            dataValidate = {};

            try {
                dataValidate = JSON.parse(dataValidateString);

                if (dataValidate.hasOwnProperty('maxlength')) {
                    var maxLength = dataValidate.maxlength;

                    if (maxLength && count.length) {
                        input.on('change keyup paste', function () {
                            var length = $(this).val().length,
                                left = maxLength - length;

                            count.text(left);
                            counter.toggleClass('hidden', length);
                        });
                    }
                }
            }
            catch (e){}
        }
    });
    return $.mage.customOption;
});
