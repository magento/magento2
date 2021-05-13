/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'Magento_Paypal/js/view/paylater-default',
    'Magento_Checkout/js/model/quote',
    'domReady!'
], function (
    $,
    ko,
    Component,
    quote
) {
    'use strict';

    var payLaterEnabled = window.checkoutConfig.payment.paypalPayLater.enabled,
        payLaterConfig = window.checkoutConfig.payment.paypalPayLater.config;

    return Component.extend({
        defaults: {
            template: 'Magento_Paypal/paylater',
            sdkUrl: payLaterEnabled ? payLaterConfig.sdkUrl : '',
            attributes: payLaterConfig.attributes,
            amount: ko.observable(),
            style: 'margin-bottom: 10px;'
        },

        /**
         * Initialize
         *
         * @returns {*}
         */
        initialize: function () {
            this._super();
            this.updateAmount();

            return this;
        },

        /**
         * Update amount
         */
        updateAmount: function () {
            var amount = this.amount;

            quote.totals.subscribe(function (newValue) {
                amount(newValue['base_grand_total']);
            });
        }
    });
});
