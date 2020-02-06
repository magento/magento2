/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*jscs:disable jsDoc*/

define([
    'squire',
    'ko'
], function (Squire, ko) {
    'use strict';

    var injector,
        mocks = {
            'Magento_Checkout/js/model/quote': {
                totals: ko.observable({
                    items: [],
                    subtotal: 0
                }),
            },
            'Magento_Customer/js/customer-data': {
                get: jasmine.createSpy().and.returnValue(
                    ko.observable({
                        subtotalAmount: 0
                    })
                ),
                reload: jasmine.createSpy()
            }
        };

    beforeEach(function () {
        injector = new Squire();
        injector.mock(mocks);
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Magento_Checkout/js/model/totals', function () {

        it('does not reload cart in customer data when its subtotal is the same as the one in the quote', function (done) {
            mocks['Magento_Checkout/js/model/quote'].totals({
                items: [],
                subtotal: 0
            });
            injector.require(['Magento_Checkout/js/model/totals'], function () {
                expect(mocks['Magento_Customer/js/customer-data'].reload).not.toHaveBeenCalled();
                done();
            });
        });

        it('reloads cart in customer data when its subtotal is different then the one in the quote', function (done) {
            mocks['Magento_Checkout/js/model/quote'].totals({
                items: [],
                subtotal: 1
            });
            injector.require(['Magento_Checkout/js/model/totals'], function () {
                expect(mocks['Magento_Customer/js/customer-data'].reload).toHaveBeenCalled();
                done();
            });
        });

        describe('totals property', function () {
            it('exposes quote totals observable', function (done) {
                injector.require(['Magento_Checkout/js/model/totals'], function (totals) {
                    expect(totals.totals).toBe(mocks['Magento_Checkout/js/model/quote'].totals);
                    done();
                });
            });
        });

        describe('isLoading property', function () {
            it('is an observable', function (done) {
                injector.require(['Magento_Checkout/js/model/totals'], function (totals) {
                    expect(typeof totals.isLoading.subscribe).toBe('function');
                    done();
                });
            });
        });

        describe('getItems method', function () {
            it('is a function', function (done) {
                injector.require(['Magento_Checkout/js/model/totals'], function (totals) {
                    expect(typeof totals.getItems).toBe('function');
                    done();
                });
            });

            it('exposes quote items', function (done) {
                var totalsItems, quoteItems = [];
                mocks['Magento_Checkout/js/model/quote'].totals({
                    items: quoteItems,
                    subtotal: 0
                });
                injector.require(['Magento_Checkout/js/model/totals'], function (totals) {
                    totalsItems = totals.getItems();
                    expect(totalsItems()).toBe(quoteItems);
                    done();
                });
            });

            it('exposes updated quote items when new values are pushed', function (done) {
                var totalsItems, quoteItems = [];
                mocks['Magento_Checkout/js/model/quote'].totals({});
                injector.require(['Magento_Checkout/js/model/totals'], function (totals) {
                    mocks['Magento_Checkout/js/model/quote'].totals({
                        items: quoteItems,
                        subtotal: 0
                    });
                    totalsItems = totals.getItems();
                    expect(totalsItems()).toBe(quoteItems);
                    done();
                });
            });
        });

        describe('getSegment method', function () {
            it('is a function', function (done) {
                injector.require(['Magento_Checkout/js/model/totals'], function (totals) {
                    expect(typeof totals.getSegment).toBe('function');
                    done();
                });
            });

            it('returns null when there are no segments in quote', function (done) {
                mocks['Magento_Checkout/js/model/quote'].totals({});
                injector.require(['Magento_Checkout/js/model/totals'], function (totals) {
                    expect(totals.getSegment('test')).toBe(null);
                    done();
                });
            });

            it('returns null when segment cannot be found', function (done) {
                mocks['Magento_Checkout/js/model/quote'].totals({
                    total_segments: []
                });
                injector.require(['Magento_Checkout/js/model/totals'], function (totals) {
                    expect(totals.getSegment('test')).toBe(null);
                    done();
                });
            });

            it('returns segment when it is defined', function (done) {
                var segment = {
                    code: 'test'
                };
                mocks['Magento_Checkout/js/model/quote'].totals({
                    total_segments: [
                        segment
                    ]
                });
                injector.require(['Magento_Checkout/js/model/totals'], function (totals) {
                    expect(totals.getSegment('test')).toBe(segment);
                    done();
                });
            });
        });

    });
});
