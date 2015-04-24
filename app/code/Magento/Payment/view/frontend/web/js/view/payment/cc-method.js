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
                return _.map(window.checkoutConfig.ccAvailableTypes, function(value, key) {
                    return {
                        'value': key,
                        'type': value
                    }
                });
            },
            getCcMonths: function() {
                return _.map(window.checkoutConfig.ccMonths, function(value, key) {
                    return {
                        'value': key,
                        'month': value
                    }
                });
            },
            getCcYears: function() {
                return _.map(window.checkoutConfig.ccYears, function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            hasVerification: function() {
                return window.checkoutConfig.ccHasVerification;
            },
            hasSsCardType: function() {
                return window.checkoutConfig.ccHasSsCardType;
            },
            getSsStartYears: function() {
                return _.map(window.checkoutConfig.ccSsStartYears, function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            }
        });
    }
);
