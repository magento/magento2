/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/mage',
    'Magento_Catalog/product/view/validation',
    'catalogAddToCart'
], function($) {
    "use strict";

    $.widget('mage.productValidate', {

        options: {
            bindSubmit: false,
            radioCheckboxClosest: '.nested'
        },

        _create: function() {
            var self = this;

            this.element.mage('validation', {
                radioCheckboxClosest: this.options.radioCheckboxClosest,

                submitHandler: function (form) {
                    var widget = $(form).catalogAddToCart({
                        bindSubmit: self.options.bindSubmit
                    });

                    widget.catalogAddToCart('submitForm', $(form));
                    return false;
                }
            });
        }
    });

    return $.mage.productValidate;
});
