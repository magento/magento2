/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Paypal/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data'
    ],
    function (
        $,
        Component,
        setPaymentMethodAction,
        additionalValidators,
        quote,
        customerData
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magento_Paypal/payment/paypal-express-bml',
                billingAgreement: ''
            },

            /** Init observable variables */
            initObservable: function () {
                this._super()
                    .observe('billingAgreement');

                return this;
            },

            /** Open window with  */
            showAcceptanceWindow: function (data, event) {
                window.open(
                    $(event.target).attr('href'),
                    'olcwhatispaypal',
                    'toolbar=no, location=no,' +
                    ' directories=no, status=no,' +
                    ' menubar=no, scrollbars=yes,' +
                    ' resizable=yes, ,left=0,' +
                    ' top=0, width=400, height=350'
                );

                return false;
            },

            /** Returns payment acceptance mark link path */
            getPaymentAcceptanceMarkHref: function () {
                return window.checkoutConfig.payment.paypalExpress.paymentAcceptanceMarkHref;
            },

            /** Returns payment acceptance mark image path */
            getPaymentAcceptanceMarkSrc: function () {
                return window.checkoutConfig.payment.paypalExpress.paymentAcceptanceMarkSrc;
            },

            /** Returns billing agreement data */
            getBillingAgreementCode: function () {
                return window.checkoutConfig.payment.paypalExpress.billingAgreementCode[this.item.method];
            },

            /** Returns payment information data */
            getData: function () {
                var parent = this._super(),
                    additionalData = null;

                if (this.getBillingAgreementCode()) {
                    additionalData = {};
                    additionalData[this.getBillingAgreementCode()] = this.billingAgreement();
                }

                return $.extend(true, parent, {
                    'additional_data': additionalData
                });
            },

            /** Redirect to paypal */
            continueToPayPal: function () {
                if (additionalValidators.validate()) {
                    //update payment method information if additional data was changed
                    this.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            customerData.invalidate(['cart']);
                            $.mage.redirect(
                                window.checkoutConfig.payment.paypalExpress.redirectUrl[quote.paymentMethod().method]
                            );
                        }
                    );

                    return false;
                }
            }
        });
    }
);
