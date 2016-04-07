/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (uiRegistry, select) {
    'use strict';

    return select.extend({

        /**
         * Hide fields on coupon tab
         */
        onUpdate: function () {

            /* eslint-disable eqeqeq */
            if (this.value() != this.displayOnlyForCouponType) {
                uiRegistry.get('sales_rule_form.sales_rule_form.rule_information.use_auto_generation').checked(false);
            }

            this.enableDisableFields();
        },

        /**
         * Enable/disable fields on Coupons tab
         */
        enableDisableFields: function () {
            var selector = '[id=sales-rule-form-tab-coupons] input, [id=sales-rule-form-tab-coupons] select, ' +
                    '[id=sales-rule-form-tab-coupons] button',
                isUseAutoGenerationChecked = uiRegistry
                    .get('sales_rule_form.sales_rule_form.rule_information.use_auto_generation')
                    .checked();
            var couponType = uiRegistry
                .get('sales_rule_form.sales_rule_form.rule_information.coupon_type')
                .value();
            var disableAuto = (isUseAutoGenerationChecked && couponType == 2) || (couponType == 3)
            _.each(
                document.querySelectorAll(selector),
                function (element) {
                    element.disabled = !disableAuto;
                }
            );
        }
    });
});
