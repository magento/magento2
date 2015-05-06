/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/method-info',
        'jquery'
    ],
    function (_, methodInfo, $) {
        return methodInfo.extend({
            defaults: {
                creditCardType: '',
                creditCardExpYear: '',
                creditCardExpMonth: '',
                creditCardNumber: '',
                creditCardSsStartMonth: '',
                creditCardSsStartYear: '',
                creditCardVerificationNumber: ''
            },
            initObservable: function () {
                this._super()
                    .observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
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
                    additional_data: {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_ss_start_month': this.creditCardSsStartMonth(),
                        'cc_ss_start_year': this.creditCardSsStartYear()
                    }
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
            getCvvImage: function() {
                return window.checkoutConfig.payment.cc.cvvImage;
            },
            getSsStartYears: function() {
                return _.map(window.checkoutConfig.payment.cc.ssStartYears, function(value, key) {
                    return {
                        'value': key,
                        'year': value
                    }
                });
            },
            isShowLegend: function() {
                return false;
            },
            getCcTypeTitleByCode: function(code) {
                var title = '';
                $.each(this.getCcAvailableTypes(), function (key, value) {
                    if (value['value'] == code) {
                        title = value['type'];
                    }
                });
                return title;
            },
            formatDisplayCcNumber: function(number) {
                return 'xxxx-' + number.substr(-4);
            },
            getInfo: function() {
                return [
                    {'name': 'Credit Card Type', value: this.getCcTypeTitleByCode(this.creditCardType())},
                    {'name': 'Credit Card Number', value: this.formatDisplayCcNumber(this.creditCardNumber())}
                ];
            }
        });
    }
);
