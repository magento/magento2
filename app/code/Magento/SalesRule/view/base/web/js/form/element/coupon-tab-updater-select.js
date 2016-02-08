/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, utils, uiRegistry, Select) {
    'use strict';

    return Select.extend({
        defaults: {},

        /**
         * Hide fields on coupon tab
         */
        onUpdate: function () {

            /* eslint-disable eqeqeq */
            var isDisabled = this.value() != this.displayOnlyForCouponType ||
                !uiRegistry.get('sales_rule_form.sales_rule_form.rule_information.use_auto_generation').value(),
                selector = '[id=coupons_information_fieldset] input, [id=coupons_information_fieldset] select, ' +
                    '[id=coupons_information_fieldset] button, [id=couponCodesGrid] input, ' +
                    '[id=couponCodesGrid] select, [id=couponCodesGrid] button';

            /* eslint-enable eqeqeq */
            this._super();
            _.each(
                document.querySelectorAll(selector),
                function (element) {
                    element.disabled = isDisabled;
                }
            );
        }
    });
});
