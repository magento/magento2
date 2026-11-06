/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/* eslint max-nested-callbacks: 0 */
define([
    'squire',
    'ko'
], function (Squire, ko) {
    'use strict';

    var injector = new Squire(),
        addressList = ko.observableArray(),
        quote = {
            shippingAddress: ko.observable(null),
            isVirtual: jasmine.createSpy().and.returnValue(false),
            billingAddress: ko.observable(null),
            shippingMethod: ko.observable(null)
        },
        rejected = jasmine.createSpyObj('rejectedBillingAddresses', ['has']),
        resolver,
        defaultBilling,
        shipping;

    beforeEach(function (done) {
        defaultBilling = {
            /** Stub */
            isDefaultBilling: function () {
                return true;
            }
        };
        shipping = {
            /** Stub */
            canUseForBilling: function () {
                return true;
            },

            /** Stub */
            isDefaultShipping: function () {
                return false;
            }
        };
        addressList([defaultBilling]);
        quote.billingAddress(null);
        quote.shippingAddress(shipping);
        injector.mock({
            'Magento_Customer/js/model/address-list': addressList,
            'Magento_Checkout/js/model/quote': quote,
            'Magento_Checkout/js/model/rejected-billing-addresses': rejected,
            'Magento_Checkout/js/checkout-data': {},
            'Magento_Checkout/js/action/select-billing-address': function (address) {
                quote.billingAddress(address);
            },
            'Magento_Checkout/js/action/create-shipping-address': {},
            'Magento_Checkout/js/action/select-shipping-address': {},
            'Magento_Checkout/js/action/select-shipping-method': {},
            'Magento_Checkout/js/model/payment-service': {},
            'Magento_Checkout/js/action/select-payment-method': {}
        });
        injector.require(['Magento_Checkout/js/model/checkout-data-resolver'], function (instance) {
            resolver = instance;
            done();
        });
    });

    describe('Magento_Checkout/js/model/checkout-data-resolver', function () {
        it('applies the default billing address', function () {
            rejected.has.and.returnValue(false);
            resolver.applyBillingAddress();
            expect(quote.billingAddress()).toBe(defaultBilling);
        });

        it('does not apply a default billing address the server rejected (#41445)', function () {
            rejected.has.and.returnValue(true);
            resolver.applyBillingAddress();
            expect(rejected.has).toHaveBeenCalledWith(defaultBilling);
            expect(quote.billingAddress()).toBe(shipping);
        });
    });
});
