/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, utils, uiRegistry, Boolean) {
    'use strict';

    return Boolean.extend({
        defaults: {},

        /**
         * Hide fields on coupon tab
         */
        onUpdate: function () {

            /* eslint-disable eqeqeq */
            var isDisabled = this.value() != this.displayOnlyForCouponType;

            /* eslint-enable eqeqeq */
            this._super();
            disableEnableCouponTabInputFields(isDisabled);
        }
    });
});
