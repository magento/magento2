/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint-disable max-nested-callbacks*/
/*jscs:disable jsDoc*/
define([
    'squire',
    'ko'
], function (Squire, ko) {
    'use strict';

    describe('Magento_Checkout/js/model/checkout-data-resolver', function () {
        var injector = new Squire(),
            checkoutDataResolver = null,
            checkoutData = null,
            quote = null;

        beforeEach(function (done) {
            var mocks = {
                'Magento_Customer/js/model/address-list': ko.observableArray(),
                'Magento_Checkout/js/model/quote': {
                    shippingAddress: ko.observable(null),
                    isVirtual: jasmine.createSpy().and.returnValue(false),
                    billingAddress: ko.observable(null),
                    shippingMethod: ko.observable(null)
                },
                'Magento_Checkout/js/checkout-data': {
                    getSelectedBillingAddress: jasmine.createSpy(),
                    getBillingAddressFromData: jasmine.createSpy(),
                    getNewCustomerBillingAddress: jasmine.createSpy(),
                    setBillingAddressFromData: jasmine.createSpy()
                },
                'Magento_Checkout/js/action/create-shipping-address': {},
                'Magento_Checkout/js/action/select-shipping-address': {},
                'Magento_Checkout/js/action/select-shipping-method': {},
                'Magento_Checkout/js/model/payment-service': {},
                'Magento_Checkout/js/action/select-payment-method': {}
            };

            injector.mock(mocks);
            injector.require(['Magento_Checkout/js/model/checkout-data-resolver'], function (instance) {
                checkoutDataResolver = instance;
                quote = mocks['Magento_Checkout/js/model/quote'];
                checkoutData = mocks['Magento_Checkout/js/checkout-data'];
                done();
            });
            window.checkoutConfig = window.checkoutConfig || {};
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {
            }
            delete window.checkoutConfig.billingAddressFromData;
            delete window.checkoutConfig.isBillingAddressFromDataValid;
        });

        describe('resolveBillingAddress()', function () {
            describe(
                'billing address is resolved from checkoutConfig if it cannot be resolved from local storage',
                function () {
                    it('billing address is selected if is valid', function () {
                        var billingAddressFromData = {
                            firstname: 'John',
                            lastname: 'Doe'
                        };

                        window.checkoutConfig.isBillingAddressFromDataValid = true;
                        window.checkoutConfig.billingAddressFromData = billingAddressFromData;
                        checkoutDataResolver.resolveBillingAddress();
                        expect(checkoutData.setBillingAddressFromData).not.toHaveBeenCalledWith();
                        expect(quote.billingAddress().firstname).toEqual(billingAddressFromData.firstname);
                        expect(quote.billingAddress().lastname).toEqual(billingAddressFromData.lastname);
                    });

                    it('billing address is not selected and form is prefilled if it is not valid.', function () {
                        var billingAddressFromData = {
                            firstname: 'John',
                            lastname: 'Doe'
                        };

                        window.checkoutConfig.isBillingAddressFromDataValid = false;
                        window.checkoutConfig.billingAddressFromData = billingAddressFromData;
                        checkoutDataResolver.resolveBillingAddress();
                        expect(checkoutData.setBillingAddressFromData).toHaveBeenCalledWith(billingAddressFromData);
                        expect(quote.billingAddress()).toBeNull();
                    });
                }
            );
        });
    });
});
