/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*jscs:disable jsDoc*/
require.config({
    map: {
        '*': {
            'Magento_Checkout/js/model/resource-url-manager': 'Magento_Checkout/js/model/resource-url-manager'
        }
    }
});

define([
    'squire',
    'ko',
    'jquery'
], function (Squire, ko, $) {
    'use strict';

    var injector = new Squire(),
        result = {
            totals: 10
        },
        totals = {
            grandTotal: 5
        },
        address = {
            countryId: 'US',
            region: null,
            regionId: 'California',
            postcode: 90210
        },
        mocks = {
            'Magento_Checkout/js/model/resource-url-manager': {
                getUrlForTotalsEstimationForNewAddress: jasmine.createSpy().and.returnValue(
                    'http://example.com'
                )
            },
            'Magento_Checkout/js/model/quote': {
                shippingMethod: ko.observable({
                    'method_code': 'flatrate',
                    'carrier_code': 'flatrate'
                }
                ),
                setTotals: jasmine.createSpy()
            },
            'mage/storage': {
                post: function () {}
            },
            'Magento_Checkout/js/model/totals': {
                isLoading: jasmine.createSpy()
            },
            'Magento_Checkout/js/model/error-processor': {
                process: jasmine.createSpy()
            },
            'Magento_Checkout/js/model/cart/cache': {
                isChanged: function () {},
                get: function () {},
                set: jasmine.createSpy()
            },
            'Magento_Customer/js/customer-data': {
                get: function () {
                },
                set: jasmine.createSpy()
            }
        },
        data = {
            totals: result,
            address: address,
            cartVersion: 1,
            shippingMethodCode: 'flatrate',
            shippingCarrierCode: 'flatrate'
        },
        defaultProcessor;

    beforeEach(function (done) {
        window.checkoutConfig = {
            quoteData: {},
            storeCode: 'US'
        };
        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/model/cart/totals-processor/default'], function (Constr) {
            defaultProcessor = Constr;
            done();
        });
    });

    describe('Magento_Checkout/js/model/cart/totals-processor/default', function () {

        it('estimateTotals if data was cached', function () {
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'isChanged').and.returnValue(false);
            spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(
                ko.observable({
                    'data_id': 1
                })
            );
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'get').and.returnValue(totals);
            spyOn(mocks['mage/storage'], 'post');
            expect(defaultProcessor.estimateTotals(address)).toBeUndefined();
            expect(mocks['Magento_Checkout/js/model/quote'].setTotals).toHaveBeenCalledWith(totals);
            expect(mocks['mage/storage'].post).not.toHaveBeenCalled();
        });

        it('estimateTotals if data wasn\'t cached and request was successfully sent', function () {
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'isChanged').and.returnValue(true);
            spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(
                ko.observable({
                    'data_id': 1
                })
            );
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'get');
            spyOn(mocks['mage/storage'], 'post').and.callFake(function () {
                return new $.Deferred().resolve(result);
            });
            expect(defaultProcessor.estimateTotals(address)).toBeUndefined();
            expect(mocks['Magento_Checkout/js/model/quote'].setTotals).toHaveBeenCalledWith(totals);
            expect(mocks['Magento_Checkout/js/model/totals'].isLoading.calls.argsFor(0)[0]).toBe(true);
            expect(mocks['Magento_Checkout/js/model/totals'].isLoading.calls.argsFor(1)[0]).toBe(false);
            expect(mocks['mage/storage'].post).toHaveBeenCalled();
            expect(mocks['Magento_Checkout/js/model/cart/cache'].get).not.toHaveBeenCalled();
            expect(mocks['Magento_Checkout/js/model/cart/cache'].set).toHaveBeenCalledWith('cart-data', data);
        });

        it('estimateTotals if data wasn\'t cached and request returns error', function () {
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'isChanged').and.returnValue(true);
            spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(
                ko.observable({
                    'data_id': 1
                })
            );
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'get');
            spyOn(mocks['mage/storage'], 'post').and.callFake(function () {
                return new $.Deferred().reject('Error Message');
            });
            expect(defaultProcessor.estimateTotals(address)).toBeUndefined();
            expect(mocks['Magento_Checkout/js/model/totals'].isLoading.calls.argsFor(0)[0]).toBe(true);
            expect(mocks['Magento_Checkout/js/model/totals'].isLoading.calls.argsFor(1)[0]).toBe(false);
            expect(mocks['mage/storage'].post).toHaveBeenCalled();
            expect(mocks['Magento_Checkout/js/model/cart/cache'].get).not.toHaveBeenCalled();
            expect(mocks['Magento_Checkout/js/model/error-processor'].process).toHaveBeenCalledWith('Error Message');
        });
    });
});

