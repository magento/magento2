/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*jscs:disable jsDoc*/
/* eslint max-nested-callbacks: 0 */
define([
    'squire',
    'ko'
], function (Squire, ko) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Customer/js/customer-data': {
                get: function () {
                    return ko.observable();
                },
                set: function () {}
            }
        },
        cache;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/model/cart/cache'], function (Constr) {
            cache = Constr;
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Magento_Checkout/js/model/cart/cache', function () {
        describe('Check the "get" method', function () {
            it('Check default call with "cart-data" key', function () {
                var expectedResult = JSON.stringify(cache.cartData);

                spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(ko.observable(expectedResult));

                expect(cache.get('cart-data')).toBe(expectedResult);
                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('cart-data');
            });

            it('Get data from local storage when key does not exist.', function () {
                var expectedResult = {
                    address: 'test'
                };

                spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(ko.observable(expectedResult));

                expect(cache.get('address')).toBe('test');
                expect(cache._getAddress).toBe(undefined);
            });
        });

        describe('Check the "set" method', function () {
            it('Check the return value to be undefined', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(ko.observable({}));

                expect(cache.set('test-key')).toBe(undefined);
            });

            it('Check if the value is set properly', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(ko.observable({}));

                expect(cache.set('test-key', 'test-value')).toBe(undefined);
                expect(cache.get('test-key')).toBe('test-value');
            });

            it('Check if the object is set properly', function () {
                var testObj = {
                    address: 'test',
                    address2: 'test2'
                };

                spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(ko.observable({}));

                expect(cache.set('cart-data', testObj)).toBe(undefined);
                expect(cache.get('address')).toBe('test');
                expect(cache.get('address2')).toBe('test2');
            });
        });

        describe('Check the "clear" method', function () {
            it('Check if the "clear" method clears the value which was set', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(ko.observable({}));

                expect(cache.set('test-key', 'test-value')).toBe(undefined);
                expect(cache.get('test-key')).toBe('test-value');
                expect(cache.clear('test-key')).toBe(undefined);
                expect(cache.get('test-key')).toBe(null);
            });

            it(
                'Check if the "clear" method resets the object to default value with the "cart-data" key received',
                function () {
                    var expectedResult = JSON.stringify(cache.cartData),
                        storage = ko.observable({});

                    spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(storage);
                    spyOn(mocks['Magento_Customer/js/customer-data'], 'set').and.callFake(function (key, value) {
                        storage(value);
                    });

                    expect(cache.set('test-key', 'test-value')).toBe(undefined);
                    expect(cache.get('test-key')).toBe('test-value');
                    expect(cache.clear('cart-data')).toBe(undefined);
                    expect(cache.get('test-key')).toBe(undefined);
                    expect(JSON.stringify(cache.get('cart-data'))).toBe(expectedResult);
                }
            );
        });
    });
});
