/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/backend/validation'
], function (jQuery) {
    'use strict';

    return function (config) {
        var _config = jQuery.extend({
            element: null,
            message: '',
            uniqueClass: 'required-unique'
        }, config);

        if (typeof _config.element === 'string') {
            jQuery.validator.addMethod(
                _config.element,

                function (value, element) {
                    var inputs = jQuery(element)
                            .closest('table')
                            .find('.' + _config.uniqueClass + ':visible'),
                        valuesHash = {},
                        isValid = true;

                    inputs.each(function (el) {
                        var inputValue = inputs[el].value;

                        if (typeof valuesHash[inputValue] !== 'undefined') {
                            isValid = false;
                        }
                        valuesHash[inputValue] = el;
                    });

                    return isValid;
                },

                _config.message
            );
        }
    };
});
