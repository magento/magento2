/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['jquery', 'ko', 'Magento_Customer/js/model/customer'], function($, ko, customer) {
    var customerIsLoggedIn = customer.isLoggedIn()();
    return {
        steps: {
            'authentication': ko.observable(customerIsLoggedIn),
            'billingAddress': ko.observable(customerIsLoggedIn),
            'shippingAddress': ko.observable(false),
            'shippingMethod': ko.observable(false),
            'paymentMethod': ko.observable(false),
            'review': ko.observable(false)
        },
        toStep: function(step) {
            if (step) {
                $.each(this.steps, function(key, step) {
                    step(false);
                });
                this.steps[step](true);
            }
        },
        isShippingAddressVisible: function() {
            return this.steps.shippingAddress;
        },
        isBillingAddressVisible: function() {
            return this.steps.billingAddress;
        },
        isShippingMethodVisible: function() {
            return this.steps.shippingMethod;
        },
        isPaymentMethodVisible: function() {
            return this.steps.paymentMethod;
        },
        isReviewVisible: function() {
            return this.steps.review;
        },
        setStepVisible: function(step, flag) {
            this.steps[step](flag);
        }
    };
});
