/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/form/element/boolean'
], function (_, utils, uiRegistry, Boolean) {
    'use strict';

    return Boolean.extend({
        defaults: {},

        /**
         * Hide fields on coupon tab
         */
        onUpdate: function () {
            var isDisabled = !this.value();

            this._super();
            disableEnableCouponTabInputFields(isDisabled);
        }
    });
});
