/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/method-info'
    ],
    function (_, methodInfo) {
        return methodInfo.extend({
            defaults: {
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardSsStartMonth: '',
                creditCardSsStartYear: ''
            },
            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardSsStartMonth',
                        'creditCardSsStartYear'
                    ]);
                return this;
            },
            getData: function() {
                return {
                    'cc_type': this.creditCardType(),
                    'cc_exp_year': this.creditCardExpYear(),
                    'cc_exp_month': this.creditCardExpMonth(),
                    'cc_number': this.creditCardNumber(),
                    'cc_ss_start_month': this.creditCardSsStartMonth(),
                    'cc_ss_start_year': this.creditCardSsStartYear()
                };
            },
            getCcAvailableTypes: function() {
                return _.map(window.checkoutConfig.payment.cc.availableTypes, function(value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
            getCcMonths: function() {
                return _.map(window.checkoutConfig.payment.cc.months, function(value, key) {
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },
            getCcYears: function() {
                return _.map(window.checkoutConfig.payment.cc.years, function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            hasVerification: function() {
                return window.checkoutConfig.payment.cc.hasVerification;
            },
            hasSsCardType: function() {
                return window.checkoutConfig.payment.cc.hasSsCardType;
            },
            getSsStartYears: function() {
                return _.map(window.checkoutConfig.payment.cc.ssStartYears, function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            }
        });
    }
);
