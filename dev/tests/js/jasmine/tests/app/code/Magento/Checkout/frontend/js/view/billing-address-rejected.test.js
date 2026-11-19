/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/* eslint max-nested-callbacks: 0 */
define([
    'squire',
    'ko',
    'jquery'
], function (Squire, ko, $) {
    'use strict';

    var injector = new Squire(),
        checkoutData = jasmine.createSpyObj('checkoutData', [
            'setSelectedBillingAddress',
            'setNewCustomerBillingAddress'
        ]),
        rejectedBillingAddresses = jasmine.createSpyObj('rejectedBillingAddresses', ['add']),
        messageList = jasmine.createSpyObj('messageList', ['addErrorMessage']),
        quote = {
            /** Stub */
            getQuoteId: function () {},

            billingAddress: ko.observable(null),
            shippingAddress: ko.observable(null),
            paymentMethod: ko.observable(null),

            /** Stub */
            isVirtual: function () {
                return false;
            }
        },
        request,
        enteredAddress,
        mocks = {
            'Magento_Checkout/js/checkout-data': checkoutData,
            'Magento_Customer/js/customer-data': {
                /** Stub */
                get: function () {}
            },
            'Magento_Checkout/js/model/quote': quote,
            'Magento_Checkout/js/model/rejected-billing-addresses': rejectedBillingAddresses,
            'Magento_Ui/js/model/messageList': messageList,
            'Magento_Checkout/js/action/set-billing-address': jasmine.createSpy('setBillingAddress')
                .and.callFake(function () {
                    return request;
                }),
            'Magento_Checkout/js/action/create-billing-address': function (data) {
                enteredAddress = {
                    telephone: data.telephone,

                    /** Stub */
                    getKey: function () {
                        return 'new-customer-billing-address';
                    },

                    /** Stub */
                    getCacheKey: function () {
                        return 'billing';
                    }
                };

                return enteredAddress;
            },
            'Magento_Checkout/js/action/select-billing-address': function (selected) {
                quote.billingAddress(selected);
            },
            'Magento_Checkout/js/model/address-converter': {
                /** Stub */
                quoteAddressToFormAddressData: function () {
                    return {};
                }
            }
        },
        billingAddress;

    /**
     * @param {String} key
     * @param {*} customerAddressId
     * @return {Object}
     */
    function savedAddress(key, customerAddressId) {
        return {
            customerAddressId: customerAddressId,

            /** Stub */
            getKey: function () {
                return key;
            },

            /** Stub */
            getCacheKey: function () {
                return key;
            }
        };
    }

    beforeEach(function (done) {
        window.checkoutConfig = {
            quoteData: {},
            storeCode: 'US'
        };
        request = $.Deferred();
        quote.billingAddress(null);
        quote.shippingAddress({
            /** Stub */
            getCacheKey: function () {
                return 'shipping';
            },

            /** Stub */
            canUseForBilling: function () {
                return true;
            }
        });
        checkoutData.setSelectedBillingAddress.calls.reset();
        rejectedBillingAddresses.add.calls.reset();
        messageList.addErrorMessage.calls.reset();

        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/view/billing-address'], function (Constr) {
            billingAddress = new Constr();
            billingAddress.source = {
                /** Stub */
                get: function (path) {
                    return path === 'params.invalid' || /custom_attributes$/.test(path) ? undefined : {
                        telephone: 'N/A'
                    };
                },

                /** Stub */
                set: function () {},

                /** Stub */
                trigger: function () {}
            };
            billingAddress.isAddressFormVisible(true);
            done();
        });
    });

    describe('Magento_Checkout/js/view/billing-address', function () {
        it('reopens the form and drops the address when the server rejects it (#41445)', function () {
            billingAddress.updateAddress();
            expect(quote.billingAddress()).toBe(enteredAddress);
            expect(billingAddress.isAddressDetailsVisible()).toBeTrue();

            request.reject({
                status: 400
            });

            expect(quote.billingAddress()).toBeNull();
            expect(checkoutData.setSelectedBillingAddress).toHaveBeenCalledWith(null);
            expect(billingAddress.isAddressDetailsVisible()).toBeFalse();
            expect(rejectedBillingAddresses.add).toHaveBeenCalledWith(enteredAddress);
            expect(messageList.addErrorMessage).not.toHaveBeenCalled();
        });

        it('keeps the address when the server accepts it', function () {
            billingAddress.updateAddress();
            request.resolve();

            expect(quote.billingAddress()).toBe(enteredAddress);
            expect(checkoutData.setSelectedBillingAddress).not.toHaveBeenCalledWith(null);
            expect(billingAddress.isAddressDetailsVisible()).toBeTrue();
        });

        it('keeps the address when the request fails without a rejection', function () {
            [0, 401, 403, 404, 500, 503].forEach(function (status) {
                request = $.Deferred();
                billingAddress.updateAddress();
                request.reject({
                    status: status
                });

                expect(quote.billingAddress()).toBe(enteredAddress);
            });
            expect(rejectedBillingAddresses.add).not.toHaveBeenCalled();
        });

        it('keeps a billing address chosen after the rejected one was sent', function () {
            var other = savedAddress('other');

            billingAddress.updateAddress();
            quote.billingAddress(other);
            request.reject({
                status: 400
            });

            expect(quote.billingAddress()).toBe(other);
        });

        it('can still cancel back to the accepted address after an edit is rejected', function () {
            var accepted = savedAddress('accepted');

            quote.billingAddress(accepted);
            billingAddress.editAddress();
            billingAddress.updateAddress();
            request.reject({
                status: 400
            });

            expect(checkoutData.setSelectedBillingAddress).toHaveBeenCalledWith('accepted');
            billingAddress.cancelAddressEdit();
            expect(quote.billingAddress()).toBe(accepted);
        });

        it('remembers a rejected saved address and says how to correct it', function () {
            var saved = savedAddress('customer-address7', 7);

            billingAddress.isAddressFormVisible(false);
            billingAddress.selectedAddress(saved);
            billingAddress.updateAddress();
            request.reject({
                status: 400
            });

            expect(rejectedBillingAddresses.add).toHaveBeenCalledWith(saved);
            expect(messageList.addErrorMessage).toHaveBeenCalled();
            expect(quote.billingAddress()).toBeNull();
        });
    });
});
