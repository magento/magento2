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
            var isDisabled = this.value() != this.displayOnlyForCouponType;
            var selector = '[id=coupons_information_fieldset] input, [id=coupons_information_fieldset] select, '
                + '[id=coupons_information_fieldset] button, [id=couponCodesGrid] input, [id=couponCodesGrid] select, '
                + '[id=couponCodesGrid] button';

            this._super();
            _.each(
                document.querySelectorAll(selector),
                function(e) {
                    e.disabled = isDisabled;
                }
            );
        }
    });
});
