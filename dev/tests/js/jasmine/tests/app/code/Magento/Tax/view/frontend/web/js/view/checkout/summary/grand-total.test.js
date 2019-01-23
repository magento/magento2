/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */
// jscs:disable jsDoc
define(['squire', 'ko'], function (Squire, ko) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Checkout/js/model/totals': {
                totals: jasmine.createSpy()
            },
            'Magento_Catalog/js/price-utils': {
                formatPrice: function () {
                }
            }
        },
        obj;

    beforeEach(function (done) {
        window.checkoutConfig = {
            quoteData: {}
        };
        injector.mock(mocks);
        injector.require(['Magento_Tax/js/view/checkout/summary/grand-total'], function (Constr) {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: ''
            });
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Magento_Tax/js/view/checkout/summary/grand-total', function () {
        describe('"getGrandTotalExclTax" method', function () {
            it('Check if totals object empty.', function () {
                expect(obj.getGrandTotalExclTax()).toBe(0);
            });
            it('Check if totals exists.', function () {
                var totalsData = {
                    'grand_total': 10
                };

                obj.totals = ko.observable(totalsData);
                spyOn(mocks['Magento_Catalog/js/price-utils'], 'formatPrice')
                    .and.returnValue(10);
                expect(obj.getGrandTotalExclTax()).toBe(10);
            });
        });
    });
    describe('Magento_Tax/js/view/checkout/summary/grand-total', function () {
        describe('"isBaseGrandTotalDisplayNeeded" method', function () {
            it('Check if totals object empty.', function () {
                expect(obj.isBaseGrandTotalDisplayNeeded()).toBe(false);
            });
            it('Check if base currency not equal to quote currency.', function () {
                var totalsData = {
                    'base_currency_code': 'USD',
                    'quote_currency_code': 'EUR'
                };

                obj.totals = ko.observable(totalsData);
                expect(obj.isBaseGrandTotalDisplayNeeded()).toBe(true);
            });
            it('Check if base currency equal to quote currency.', function () {
                var totalsData = {
                    'base_currency_code': 'USD',
                    'quote_currency_code': 'USD'
                };

                obj.totals = ko.observable(totalsData);
                expect(obj.isBaseGrandTotalDisplayNeeded()).toBe(false);
            });
        });
    });

});
