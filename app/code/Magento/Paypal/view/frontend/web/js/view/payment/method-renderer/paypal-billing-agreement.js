/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'mage/validation'
], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Paypal/payment/paypal_billing_agreement-form',
            selectedBillingAgreement: ''
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super()
                .observe('selectedBillingAgreement');

            return this;
        },

        /**
         * @return {*}
         */
        getTransportName: function () {
            return window.checkoutConfig.payment.paypalBillingAgreement.transportName;
        },

        /**
         * @return {*}
         */
        getBillingAgreements: function () {
            return window.checkoutConfig.payment.paypalBillingAgreement.agreements;
        },

        /**
         * @return {Object}
         */
        getData: function () {
            var additionalData = null;

            if (this.getTransportName()) {
                additionalData = {};
                additionalData[this.getTransportName()] = this.selectedBillingAgreement();
            }

            return {
                'method': this.item.method,
                'additional_data': additionalData
            };
        },

        /**
         * @return {jQuery}
         */
        validate: function () {
            var form = '#billing-agreement-form';

            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
