/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */
define([
    'squire',
    'ko'
], function (Squire, ko) {
    'use strict';

    var injector = new Squire(),
        checkoutData = jasmine.createSpyObj('checkoutData', ['setNewCustomerBillingAddress']),
        mocks = {
            'Magento_Checkout/js/checkout-data': checkoutData,
            'Magento_Customer/js/customer-data': {
                /** Stub */
                get: function () {}
            },
            'Magento_Checkout/js/model/quote': {
                /** Stub */
                getQuoteId: function () {},

                billingAddress: ko.observable(null),

                /** Stub */
                isVirtual: function () {
                    return false;
                },

                shippingAddress: ko.observable(null),
                paymentMethod: ko.observable(null)
            }
        },
        lastSelectedBillingAddress = {
            city: 'Culver City',
            company: 'Magento',
            country_id: 'US',// jscs:ignore requireCamelCaseOrUpperCaseIdentifiers
            firstname: 'John',
            lastname: 'Doe',
            postcode: '90230',
            region: '',
            region_id: '12',// jscs:ignore requireCamelCaseOrUpperCaseIdentifiers
            street: {
                0: '6161 West Centinela Avenue',
                1: ''
            },
            telephone: '+15555555555'
        },
        billingAddress;

    beforeEach(function (done) {
        window.checkoutConfig = {
            quoteData: {},
            storeCode: 'US'
        };

        spyOn(mocks['Magento_Checkout/js/model/quote'], 'billingAddress').and.returnValue(lastSelectedBillingAddress);

        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/view/billing-address'], function (Constr) {
            billingAddress = new Constr;

            billingAddress.source = {
                /** Stub */
                get: function () {
                    return [];
                },

                /** Stub */
                set: function () {},

                /** Stub */
                trigger: function () {}
            };

            done();
        });
    });

    describe('Magento_Checkout/js/view/billing-address', function () {
        describe('"needCancelBillingAddressChanges" method', function () {
            it('Test negative scenario', function () {
                spyOn(billingAddress, 'cancelAddressEdit');
                billingAddress.editAddress();
                billingAddress.updateAddress();
                billingAddress.needCancelBillingAddressChanges();
                expect(billingAddress.cancelAddressEdit).not.toHaveBeenCalled();
            });

            it('Test that billing address editing was canceled automatically', function () {
                spyOn(billingAddress, 'cancelAddressEdit');
                billingAddress.editAddress();
                billingAddress.needCancelBillingAddressChanges();
                expect(billingAddress.cancelAddressEdit).toHaveBeenCalled();
            });
        });

        describe('"restoreBillingAddress" method', function () {
            it('Test that lastSelectedBillingAddress was restored correctly', function () {
                billingAddress.editAddress();
                billingAddress.restoreBillingAddress();
                expect(checkoutData.setNewCustomerBillingAddress).toHaveBeenCalledWith(lastSelectedBillingAddress);
            });
        });
    });
});
