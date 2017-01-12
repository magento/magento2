/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/mage'
], function ($, mage) {
    'use strict';

    return function (config, element) {
        $(element).mage('validation', {
            errorPlacement: function (error, element) {

                if (element.parents('#product-review-table').length) {
                    $('#product-review-table').siblings(this.errorElement + '.' + this.errorClass).remove();
                    $('#product-review-table').after(error);
                } else {
                    element.after(error);
                }
            }
        });
    };
});
