/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/mage'
], function ($) {
    'use strict';

    return function (config, element) {
        $(element).mage('validation', {
            /** @inheritdoc */
            errorPlacement: function (error, el) {

                if (el.parents('#product-review-table').length) {
                    $('#product-review-table').siblings(this.errorElement + '.' + this.errorClass).remove();
                    $('#product-review-table').after(error);
                } else {
                    el.after(error);
                }
            }
        });
    };
});
