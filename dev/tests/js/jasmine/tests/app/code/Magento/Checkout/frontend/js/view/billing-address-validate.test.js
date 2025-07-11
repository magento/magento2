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
        checkoutData = jasmine.createSpyObj(
            'checkoutData',
            ['setSelectedBillingAddress', 'setNewCustomerBillingAddress']
        ),
        createBillingAddress = jasmine.createSpy('createBillingAddress').and.callFake(function () {
            return {
                getKey: function () {
                    return 'new-billing-address-key';
                }
            };
        }),
        selectBillingAddress = jasmine.createSpy('selectBillingAddress'),
        mocks = {
            'Magento_Checkout/js/checkout-data': checkoutData,
            'Magento_Checkout/js/action/create-billing-address': createBillingAddress,
            'Magento_Checkout/js/action/select-billing-address': selectBillingAddress,
            'Magento_Customer/js/model/customer': {
                isLoggedIn: function () {
                    return true;
                }
            },
            'Magento_Checkout/js/model/quote': {
                billingAddress: ko.observable(null),
                shippingAddress: ko.observable(null),
                isVirtual: function () {
                    return false;
                },
                paymentMethod: ko.observable(null)
            },
            'Magento_Customer/js/customer-data': {
                get: function () {
                    return function () {
                        return {};
                    };
                }
            }
        },
        lastSelectedBillingAddress = {
            city: 'Culver City',
            company: 'Magento',
            country_id: 'US',
            firstname: 'John',
            lastname: '',
            postcode: '90230',
            region: '',
            region_id: '12',
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
            storeCode: 'US',
            reloadOnBillingAddress: false,
            displayBillingOnPaymentMethod: true
        };

        spyOn(mocks['Magento_Checkout/js/model/quote'], 'billingAddress').and.returnValue(lastSelectedBillingAddress);

        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/view/billing-address'], function (Constr) {
            billingAddress = new Constr;

            billingAddress.source = {
                get: jasmine.createSpy('get').and.callFake(function (key) {
                    if (key === billingAddress.dataScopePrefix + '.custom_attributes') {
                        return true;
                    } else if (key === 'params.invalid') {
                        return true; // Simulate valid form data
                    } else if (key === billingAddress.dataScopePrefix) {
                        return lastSelectedBillingAddress; // Return mock address data
                    }
                    return null;
                }),
                set: jasmine.createSpy('set'),
                trigger: jasmine.createSpy('trigger').and.callFake(function (event) {
                    if (
                        event === billingAddress.dataScopePrefix + '.data.validate'
                        || event === billingAddress.dataScopePrefix + '.custom_attributes.data.validate'
                    ) {
                        billingAddress.source.set('params.invalid', false); // Simulate valid form data
                    }
                })
            };

            done();
        });
    });

    describe('Magento_Checkout/js/view/billing-address', function () {
        describe('"updateAddress" method', function () {
            it('should call updateAddresses when form is invalid with false', function () {
                billingAddress.source.set.and.callFake(function (key, value) {
                    if (key === 'params.invalid' && value === true) {
                        billingAddress.source.get.and.callFake(function () {
                            if (key === 'params.invalid') {
                                return true; // Simulate invalid form data
                            }
                            return null;
                        });
                    }
                });
                spyOn(billingAddress, 'updateAddresses');
                billingAddress.updateAddress();
                expect(billingAddress.updateAddresses).toHaveBeenCalledWith(false);
                expect(selectBillingAddress).not.toHaveBeenCalled();
            });

            it('should call updateAddresses when form is valid with true', function () {
                billingAddress.source.get.and.callFake(function (key) {
                    if (key === 'params.invalid') {
                        return false; // Simulate valid form data
                    } else if (key === billingAddress.dataScopePrefix) {
                        return lastSelectedBillingAddress; // Return mock address data
                    }
                    return null;
                });

                spyOn(billingAddress, 'updateAddresses');
                billingAddress.updateAddress();
                expect(billingAddress.updateAddresses).toHaveBeenCalledWith(true);
                expect(selectBillingAddress).toHaveBeenCalled();
            });
        });
    });
});
