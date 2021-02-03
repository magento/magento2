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

    var injector = new Squire(),
        cartData = {
            'subtotalAmount': 10
        },
        cart = ko.observable(cartData),
        cartDataTwo = {
            'subtotalAmount': NaN
        },
        cartTwo = ko.observable(cartDataTwo),
        mocks = {
            'Magento_Checkout/js/model/quote': {
                totals: ko.observable({
                    'subtotal': 4
                })
            },
            'Magento_Customer/js/customer-data': {
                get: function () {
                    return cart;
                },
                reload: jasmine.createSpy(),
                getInitCustomerData: function () {}
            }
        },
        mocksTwo = {
            'Magento_Checkout/js/model/quote': {
                totals: ko.observable({
                    'subtotal': 10
                })
            },
            'Magento_Customer/js/customer-data': {
                get: function () {
                    return cartTwo;
                },
                reload: jasmine.createSpy(),
                getInitCustomerData: function () {}
            }
        };

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Test that customer data is reloaded when quote subtotal and cart subtotal are different', function () {
        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require(['Magento_Checkout/js/model/totals'], function () {
                done();
            });
        });
        it('Test that customer data is reloaded when quote subtotal and cart subtotal are different', function () {
            expect(mocks['Magento_Checkout/js/model/quote'].totals().subtotal).toBe(4);
            expect(cart().subtotalAmount).toBe(10);
            expect(mocks['Magento_Customer/js/customer-data'].reload).toHaveBeenCalled();
        });
    });

    describe('Test that customer data is not reloaded when cart subtotal is NaN', function () {
        beforeEach(function (done) {
            injector.mock(mocksTwo);
            injector.require(['Magento_Checkout/js/model/totals'], function () {
                done();
            });
        });
        it('Test that customer data is not reloaded when cart subtotal is NaN', function () {
            expect(mocksTwo['Magento_Checkout/js/model/quote'].totals().subtotal).toBe(10);
            expect(cartTwo().subtotalAmount).toBeNaN();
            expect(mocksTwo['Magento_Customer/js/customer-data'].reload).not.toHaveBeenCalled();
        });
    });
});

