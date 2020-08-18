/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
/*jscs:disable jsDoc*/
define([
    'squire', 'jquery', 'ko'
], function (Squire, $, ko) {
    'use strict';

    describe('Magento_Checkout/js/empty-cart', function () {
        var injector = new Squire(),
            cartData = ko.observable({}),
            mocks = {
                'Magento_Customer/js/customer-data': {
                    get: jasmine.createSpy('get', function () {
                        return cartData;
                    }).and.callThrough(),
                    reload: jasmine.createSpy(),
                    getInitCustomerData: function () {}
                }
            },
            deferred,
            emptyCart;

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require(['Magento_Checkout/js/empty-cart'], function (instance) {
                emptyCart = instance;
                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {}

            cartData({});
        });

        describe('Check Cart data preparation process', function () {
            it('Tests that Cart data is NOT checked before initialization', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'getInitCustomerData').and.callFake(function () {
                    deferred = $.Deferred();

                    return deferred.promise();
                });
                expect(emptyCart()).toBe(undefined);

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('cart');
                expect(mocks['Magento_Customer/js/customer-data'].getInitCustomerData).toHaveBeenCalled();
                expect(mocks['Magento_Customer/js/customer-data'].reload).not.toHaveBeenCalled();
            });

            it('Tests that Cart data does NOT reload if there are no items in it', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'getInitCustomerData').and.callFake(function () {
                    deferred = $.Deferred();

                    deferred.resolve();

                    return deferred.promise();
                });
                cartData({
                    items: []
                });
                emptyCart();

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('cart');
                expect(mocks['Magento_Customer/js/customer-data'].reload).not.toHaveBeenCalled();
            });

            it('Tests that Cart data is checked only after initialization', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'getInitCustomerData').and.callFake(function () {
                    deferred = $.Deferred();

                    return deferred.promise();
                });
                cartData({
                    items: [1]
                });
                emptyCart();

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('cart');
                expect(mocks['Magento_Customer/js/customer-data'].reload).not.toHaveBeenCalled();

                deferred.resolve();

                expect(mocks['Magento_Customer/js/customer-data'].reload).toHaveBeenCalledWith(['cart'], false);
            });

            it('Tests that Cart data reloads if it has items', function () {
                spyOn(mocks['Magento_Customer/js/customer-data'], 'getInitCustomerData').and.callFake(function () {
                    deferred = $.Deferred();

                    deferred.resolve();

                    return deferred.promise();
                });
                cartData({
                    items: [1]
                });
                emptyCart();

                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('cart');
                expect(mocks['Magento_Customer/js/customer-data'].reload).toHaveBeenCalledWith(['cart'], false);
            });
        });
    });
});
