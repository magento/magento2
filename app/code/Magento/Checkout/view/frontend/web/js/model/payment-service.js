/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['ko'],
    function (ko) {
        return {
            availablePaymentMethods: ko.observableArray([]),
            setPaymentMethods: function(methods) {
                this.availablePaymentMethods(methods);
            },
            getAvailablePaymentMethods: function () {
                return this.availablePaymentMethods;
            },
            getPaymentMethodTitle: function(code) {
                var paymentMethodTitle = '';
                if (code) {
                    var paymentMethodsArray = this.availablePaymentMethods();
                    paymentMethodsArray.forEach(function (entry) {
                        if (entry.code == code) {
                            paymentMethodTitle = entry.title
                        }
                    });
                }
                return paymentMethodTitle
            }
        }
    }
);
