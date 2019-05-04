/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
/*jscs:disable jsDoc*/

define([
    'squire',
    'jquery',
    'ko'
], function (Squire, $, ko) {
    'use strict';

    describe('Magento_Checkout/js/sidebar', function () {
        var injector = new Squire(),
            mocks = {
                'Magento_Customer/js/customer-data': {
                    get: function () {
                        return ko.observable();
                    }
                }
            },
            sidebar,
            cartData = {
                'items': [
                    {
                        'item_id': 1,
                        'product_sku': 'bundle',
                        'product_id': '1'
                    },
                    {
                        'item_id': 5,
                        'product_sku': 'simple',
                        'product_id': '5'
                    },
                    {
                        'item_id': 7,
                        'product_sku': 'configurable',
                        'product_id': '7'
                    }
                ]
            },
            cart = ko.observable(cartData);

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require(['Magento_Checkout/js/sidebar'], function (Constr) {
                sidebar = new Constr;
                done();
            });
        });

        describe('Check remove mini-cart item callback.', function () {
            beforeEach(function () {
                spyOn(jQuery.fn, 'trigger');
                spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(cart);
            });

            it('Method "_removeItemAfter" is defined', function () {
                expect(sidebar._removeItemAfter).toBeDefined();
            });

            it('Cart item is exists', function () {
                var elem = $('<input>').data('cart-item', 5);

                sidebar._removeItemAfter(elem);
                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('cart');
                expect(jQuery('body').trigger).toHaveBeenCalledWith('ajax:removeFromCart', {
                    'productIds': ['5']
                });
            });

            it('Cart item doesn\'t exists', function () {
                var elem = $('<input>').data('cart-item', 100);

                sidebar._removeItemAfter(elem);
                expect(jQuery('body').trigger).not.toHaveBeenCalled();
            });
        });

        describe('Check update item quantity callback.', function () {
            beforeEach(function () {
                spyOn(jQuery.fn, 'trigger');
                spyOn(mocks['Magento_Customer/js/customer-data'], 'get').and.returnValue(cart);
            });

            it('Method "_updateItemQtyAfter" is defined', function () {
                expect(sidebar._updateItemQtyAfter).toBeDefined();
            });

            it('Cart item is exists', function () {
                var elem = $('<input>').data('cart-item', 5);

                spyOn(sidebar, '_hideItemButton');

                sidebar._updateItemQtyAfter(elem);
                expect(mocks['Magento_Customer/js/customer-data'].get).toHaveBeenCalledWith('cart');
                expect(jQuery('body').trigger).toHaveBeenCalledWith('ajax:updateCartItemQty');
                expect(sidebar._hideItemButton).toHaveBeenCalledWith(elem);
            });

            it('Cart item doesn\'t exists', function () {
                var elem = $('<input>').data('cart-item', 100);

                spyOn(sidebar, '_hideItemButton');

                sidebar._updateItemQtyAfter(elem);
                expect(jQuery('body').trigger).not.toHaveBeenCalled();
                expect(sidebar._hideItemButton).toHaveBeenCalledWith(elem);
            });
        });
    });
});
