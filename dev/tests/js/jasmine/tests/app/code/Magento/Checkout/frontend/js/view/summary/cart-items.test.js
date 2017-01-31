/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */
// jscs:disable jsDoc
define(['squire'], function (Squire) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Checkout/js/model/totals': {
                totals: function () {
                    return {
                        'items_qty': 13.0765
                    };
                },
                getItems: function () {
                    var observable = function () {
                        return [{
                            itemId: 1
                        }, {
                            itemId: 2
                        }];
                    };

                    observable.subscribe = function () {};

                    return observable;
                }
            },
            'Magento_Checkout/js/model/quote': {
                isVirtual: function () {
                    return false;
                }
            },
            'Magento_Checkout/js/model/step-navigator': {
                isProcessed: function () {
                    return true;
                }
            }
        },
        obj;

    beforeEach(function (done) {
        window.checkoutConfig = {
            maxCartItemsToDisplay: 1,
            cartUrl: 'url/to/cart'
        };

        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/view/summary/cart-items'], function (Constr) {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: '',
                itemsTestStorage: [],

                /**
                 * @param {*} items
                 */
                items: function (items) {
                    this.itemsTestStorage = items;
                }
            });
            done();
        });
    });

    describe('Magento_Checkout/js/view/summary/cart-items', function () {
        describe('"getItemsQty" method', function () {
            it('Check for return value.', function () {
                expect(obj.getItemsQty()).toBe(13.0765);
            });
        });

        describe('"isItemsBlockExpanded" method', function () {
            it('Check for return value.', function () {
                expect(obj.isItemsBlockExpanded()).toBeTruthy();
            });
        });

        describe('"getCartLineItemsCount" method', function () {
            it('Check for return value.', function () {
                expect(obj.getCartLineItemsCount()).toBe(2);
            });
        });

        describe('"setItems" method', function () {
            it('Check for return value.', function () {
                var items = [{
                    itemId: 1
                }, {
                    itemId: 2
                }],
                    expectedResult = JSON.stringify([{
                        itemId: 2
                    }]);

                expect(obj.setItems(items)).toBeUndefined();
                expect(JSON.stringify(obj.itemsTestStorage)).toBe(expectedResult);
            });
        });
    });
});
