/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        "mage/translate",
        'Magento_Checkout/js/view/payment/method-info'
    ],
    function ($, $t, methodInfo) {
        return methodInfo.extend({
            defaults: {
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: ''
            },
            initObservable: function () {
                this._super()
                    .observe(['creditCardType', 'creditCardExpYear', 'creditCardExpMonth', 'creditCardNumber']);
                return this;
            },
            getData: function() {
                return {
                    'cc_type': this.creditCardType(),
                    'cc_exp_year': this.creditCardExpYear(),
                    'cc_exp_month': this.creditCardExpMonth(),
                    'cc_number': this.creditCardNumber()
                };
            },
            getCcAvailableTypes: function() {
                return window.checkoutConfig.ccAvailableTypes;
            },

        });
    }
);
