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
            sidebar;

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require(['Magento_Checkout/js/sidebar'], function (Constr) {
                sidebar = new Constr;
                done();
            });
        });

        describe('Check remove mini-cart item callback.', function () {
            var cartData = {
                    'items': [
                        {
                            'item_id': 1,
                            'product_sku': 'bundle'
                        },
                        {
                            'item_id': 5,
                            'product_sku': 'simple'
                        },
                        {
                            'item_id': 7,
                            'product_sku': 'configurable'
                        }
                    ]
                },
                cart = ko.observable(cartData);

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
                expect(jQuery('body').trigger).toHaveBeenCalledWith('ajax:removeFromCart', 'simple');
            });

            it('Cart item doesn\'t exists', function () {
                var elem = $('<input>').data('cart-item', 100);

                sidebar._removeItemAfter(elem);
                expect(jQuery('body').trigger).not.toHaveBeenCalled();
            });
        });
    });
});
