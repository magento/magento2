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
        }
    });
});
