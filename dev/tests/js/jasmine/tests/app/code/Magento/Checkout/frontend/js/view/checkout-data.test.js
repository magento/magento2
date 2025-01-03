/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Checkout/js/checkout-data', function () {
        let checkoutData,
            cacheKey = 'checkout-data',
            testData = {
                shippingAddressFromData: {base: {address1: 'address1'}}
            },

            /** Stub */
            getStorageData = function () {
                return function () {
                    return testData;
                };
            },
            injector = new Squire(),
            mocks = {
                'Magento_Customer/js/customer-data': {
                    /** Method stub. */
                    set: jasmine.createSpy(),
                    get: jasmine.createSpy().and.callFake(getStorageData)
                },
                'jquery/jquery-storageapi': jasmine.createSpy(),
                'mageUtils': jasmine.createSpy()
            };

        window.checkoutConfig = {
            storeCode: 'base'
        };

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Checkout/js/checkout-data'
            ], function (Constructor) {
                checkoutData = Constructor;
                done();
            });
        });

        it('should save selected shipping address per website', function () {
            checkoutData.setShippingAddressFromData({address1: 'address1'});
            expect(mocks['Magento_Customer/js/customer-data'].set).
                toHaveBeenCalledWith(cacheKey, jasmine.objectContaining(testData));
        });

        it('should get shipping address from data per website', function () {
            let address = checkoutData.getShippingAddressFromData();

            expect(address).toEqual(testData.shippingAddressFromData.base);
        });

        it('should save new customer shipping address per website', function () {
            checkoutData.setNewCustomerShippingAddress({address1: 'address1'});
            expect(mocks['Magento_Customer/js/customer-data'].set).
                toHaveBeenCalledWith(cacheKey, jasmine.objectContaining(testData));
        });

        it('should get new customer shipping address from data per website', function () {
            let address = checkoutData.getNewCustomerShippingAddress();

            expect(address).toEqual(testData.shippingAddressFromData.base);
        });
    });
});
