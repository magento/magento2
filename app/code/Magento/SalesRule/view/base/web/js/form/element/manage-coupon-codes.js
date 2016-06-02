/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/components/fieldset',
    'Magento_Ui/js/lib/view/utils/async'
], function (_, uiRegistry, fieldset, async) {
    'use strict';

    return fieldset.extend({

        /*eslint-disable no-unused-vars*/
        /**
         * Initialize element
         *
         * @returns {Abstract} Chainable
         */
        initialize: function (elems, position) {
            var obj = this;

            this._super();

            async.async('#sales-rule-form-tab-coupons', document.getElementById('container'), function (node) {
                var useAutoGeneration = uiRegistry.get(
                    'sales_rule_form.sales_rule_form.rule_information.use_auto_generation'
                );

                useAutoGeneration.on('checked', function () {
                    obj.enableDisableFields();
                });
                obj.enableDisableFields();
            });

            return this;
        },

        /*eslint-enable no-unused-vars*/
        /*eslint-disable lines-around-comment*/

        /**
         * Enable/disable fields on Coupons tab
         */
        enableDisableFields: function () {
            var selector,
                isUseAutoGenerationChecked,
                couponType,
                disableAuto;

            selector = '[id=sales-rule-form-tab-coupons] input, [id=sales-rule-form-tab-coupons] select, ' +
                    '[id=sales-rule-form-tab-coupons] button';
            isUseAutoGenerationChecked = uiRegistry
                    .get('sales_rule_form.sales_rule_form.rule_information.use_auto_generation')
                    .checked();
            couponType = uiRegistry
                .get('sales_rule_form.sales_rule_form.rule_information.coupon_type')
                .value();
            /**
             * \Magento\Rule\Model\AbstractModel::COUPON_TYPE_AUTO
             */
            disableAuto = couponType === 3 || isUseAutoGenerationChecked;
            _.each(
                document.querySelectorAll(selector),
                function (element) {
                    element.disabled = !disableAuto;
                }
            );
        }
    });
});
