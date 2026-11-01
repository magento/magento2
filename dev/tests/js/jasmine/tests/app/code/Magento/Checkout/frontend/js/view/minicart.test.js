/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

/* eslint max-nested-callbacks: 0 */
// jscs:disable jsDoc
define(['squire'], function (Squire) {
    'use strict';

    var injector = new Squire(),
        obj;

    beforeEach(function (done) {
        window.checkout = {
            maxItemsToDisplay: 1
        };

        injector.require(['Magento_Checkout/js/view/minicart'], function (Constr) {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: '',
                cart: {
                    items: function () {
                        return [
                            {
                                itemId: 1
                            },
                            {
                                itemId: 2
                            }
                        ];
                    }
                },
                itemRenderer: {
                    'simpleProductType': 'customRenderer'
                }
            });
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch {}
    });

    describe('Magento_Checkout/js/view/minicart', function () {
        describe('"getCartItems" method', function () {
            it('Check for return value.', function () {
                var expectedResult = JSON.stringify([
                    {
                        itemId: 2
                    }
                ]);

                expect(obj.getCartItems().length).toBe(1);
                expect(JSON.stringify(obj.getCartItems())).toBe(expectedResult);
            });
        });

        describe('"getCartLineItemsCount" method', function () {
            it('Check for return value.', function () {
                expect(obj.getCartLineItemsCount()).toBe(2);
            });
        });

        describe('"getItemRenderer" method', function () {
            describe('Returns different renderers by product type', function () {
                it('Check for default renderer.', function () {
                    expect(obj.getItemRenderer('undefinedProductType')).toBe('defaultRenderer');
                });

                it('Check for custom renderer.', function () {
                    expect(obj.getItemRenderer('simpleProductType')).toBe('customRenderer');
                });
            });
        });

        describe('"getCartParam" method', function () {
            it('Check for return value.', function () {
                var expectedResult = JSON.stringify([
                    {
                        itemId: 1
                    },
                    {
                        itemId: 2
                    }
                ]);

                expect(obj.getCartParam('items').length).toBe(2);
                expect(JSON.stringify(obj.getCartParam('items'))).toBe(expectedResult);
            });
        });

        describe('"minicartSelector" property', function () {
            it('Has default minicart selector.', function () {
                expect(obj.minicartSelector).toBe('[data-block="minicart"]');
            });

            it('Can be overridden via config.', function (done) {
                injector.require(['Magento_Checkout/js/view/minicart'], function (Constr) {
                    var customObj = new Constr({
                        provider: 'provName',
                        name: '',
                        index: '',
                        minicartSelector: '[data-block="minicart-footer"]',
                        cart: {},
                        itemRenderer: {}
                    });

                    expect(customObj.minicartSelector).toBe('[data-block="minicart-footer"]');
                    done();
                });
            });
        });

        describe('"addToCartCalls" property', function () {
            it('Is an instance-level property initialized to 0.', function () {
                expect(obj.addToCartCalls).toBe(0);
            });

            it('Each instance maintains its own counter.', function (done) {
                injector.require(['Magento_Checkout/js/view/minicart'], function (Constr) {
                    var secondObj = new Constr({
                        provider: 'provName',
                        name: '',
                        index: '',
                        cart: {},
                        itemRenderer: {}
                    });

                    obj.addToCartCalls = 5;
                    expect(secondObj.addToCartCalls).toBe(0);
                    done();
                });
            });
        });

        describe('"initSidebar" method', function () {
            it('Is defined as an instance method.', function () {
                expect(typeof obj.initSidebar).toBe('function');
            });
        });

        describe('"closeMinicart" method', function () {
            it('Is defined as an instance method.', function () {
                expect(typeof obj.closeMinicart).toBe('function');
            });
        });
    });
});
