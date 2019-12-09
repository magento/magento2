/**
 * Copyright © Magento, Inc. All rights reserved.
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
        address = {
            countryId: 'US',
            region: null,
            regionId: 'California',
            postcode: 90210
        },
        data = {
            totals: result,
            address: address,
            cartVersion: 1,
            shippingMethodCode: null,
            shippingCarrierCode: null
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
                }),
                totals: ko.observable({
                    'subtotal': 4
                }),
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

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Magento_Checkout/js/model/cart/totals-processor/default', function () {

        it('estimateTotals if data wasn\'t cached and request was successfully sent', function () {
            var deferral = new $.Deferred();

            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'isChanged').and.returnValue(true);
            spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(
                ko.observable({
                    'data_id': 1
                })
            );
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'get');
            spyOn(mocks['mage/storage'], 'post').and.callFake(function () {
                data.shippingMethodCode = mocks['Magento_Checkout/js/model/quote'].shippingMethod()['method_code'];
                data.shippingCarrierCode = mocks['Magento_Checkout/js/model/quote'].shippingMethod()['carrier_code'];

                return deferral.resolve(result);
            });
            expect(defaultProcessor.estimateTotals(address)).toBe(deferral);
            expect(mocks['Magento_Checkout/js/model/quote'].setTotals).toHaveBeenCalledWith(result);
            expect(mocks['Magento_Checkout/js/model/totals'].isLoading.calls.argsFor(0)[0]).toBe(true);
            expect(mocks['Magento_Checkout/js/model/totals'].isLoading.calls.argsFor(1)[0]).toBe(false);
            expect(mocks['mage/storage'].post).toHaveBeenCalled();
        });

        it('estimateTotals if data wasn\'t cached and request returns error', function () {
            var deferral = new $.Deferred();

            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'isChanged').and.returnValue(true);
            spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(
                ko.observable({
                    'data_id': 1
                })
            );
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'get');
            spyOn(mocks['mage/storage'], 'post').and.callFake(function () {
                return deferral.reject('Error Message');
            });
            expect(defaultProcessor.estimateTotals(address)).toBe(deferral);
            expect(mocks['Magento_Checkout/js/model/totals'].isLoading.calls.argsFor(0)[0]).toBe(true);
            expect(mocks['Magento_Checkout/js/model/totals'].isLoading.calls.argsFor(1)[0]).toBe(false);
            expect(mocks['mage/storage'].post).toHaveBeenCalled();
            expect(mocks['Magento_Checkout/js/model/error-processor'].process).toHaveBeenCalledWith('Error Message');
        });
    });
});

