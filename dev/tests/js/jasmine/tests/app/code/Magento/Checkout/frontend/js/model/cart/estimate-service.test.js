/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*jscs:disable jsDoc*/
define([
    'squire',
    'ko'
], function (Squire, ko) {
    'use strict';

    var injector = new Squire(),
        rates = 'flatrate',
        mocks = {
            'Magento_Checkout/js/model/quote': {
                shippingAddress: ko.observable(),
                isVirtual: function () {},
                billingAddress: ko.observable(),
                shippingMethod: ko.observable()

            },
            'Magento_Checkout/js/model/shipping-rate-processor/new-address': {
                getRates: jasmine.createSpy()
            },
            'Magento_Checkout/js/model/cart/totals-processor/default': {
                estimateTotals: jasmine.createSpy()
            },
            'Magento_Checkout/js/model/shipping-service': {
                setShippingRates: function () {},
                getShippingRates: function () {
                    return ko.observable(rates);
                }
            },
            'Magento_Checkout/js/model/cart/cache': {
                isChanged: function () {},
                get: jasmine.createSpy().and.returnValue(rates),
                set: jasmine.createSpy()
            },
            'Magento_Customer/js/customer-data': {
                get: jasmine.createSpy().and.returnValue(
                    ko.observable({
                        'data_id': 1
                    })
                )
            }
        },
        estimateService;

    window.checkoutConfig = {
        quoteData: {},
        storeCode: 'US'
    };

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/model/cart/estimate-service'], function (Constr) {
            estimateService = Constr;
            done();
        });

    });

    describe('Magento_Checkout/js/model/cart/estimate-service', function () {

        it('test subscribe when billingAddress was changed for  virtual quote', function () {
            spyOn(mocks['Magento_Checkout/js/model/quote'], 'isVirtual').and.returnValue(false);
            mocks['Magento_Checkout/js/model/quote'].billingAddress({
                id: 5,
                getType: function () {
                    return 'address_type_test';
                }
            });
            expect(mocks['Magento_Checkout/js/model/cart/totals-processor/default'].estimateTotals)
                .not.toHaveBeenCalled();
        });

        it('test subscribe when shipping address wasn\'t changed for not virtual quote', function () {
            spyOn(mocks['Magento_Checkout/js/model/quote'], 'isVirtual').and.returnValue(false);
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'isChanged').and.returnValue(false);
            spyOn(mocks['Magento_Checkout/js/model/shipping-service'], 'setShippingRates');
            mocks['Magento_Checkout/js/model/quote'].shippingAddress({
                id: 2,
                getType: function () {
                    return 'address_type_test';
                }
            });

            expect(mocks['Magento_Checkout/js/model/shipping-service'].setShippingRates).toHaveBeenCalledWith(rates);
            expect(mocks['Magento_Checkout/js/model/cart/totals-processor/default'].estimateTotals).not
                .toHaveBeenCalled();
            expect(mocks['Magento_Checkout/js/model/shipping-rate-processor/new-address'].getRates)
                .not.toHaveBeenCalled();
        });

        it('test subscribe when shipping address was changed for virtual quote', function () {
            spyOn(mocks['Magento_Checkout/js/model/quote'], 'isVirtual').and.returnValue(true);
            mocks['Magento_Checkout/js/model/quote'].shippingAddress({
                id: 1,
                getType: function () {
                    return 'address_type_test';
                }
            });
            expect(mocks['Magento_Checkout/js/model/cart/totals-processor/default'].estimateTotals)
                .toHaveBeenCalled();
            expect(mocks['Magento_Checkout/js/model/shipping-rate-processor/new-address'].getRates)
                .not.toHaveBeenCalled();
        });

        it('test subscribe when shipping address was changed for not virtual quote', function () {
            spyOn(mocks['Magento_Checkout/js/model/quote'], 'isVirtual').and.returnValue(false);
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'isChanged').and.returnValue(
                true
            );
            spyOn(mocks['Magento_Checkout/js/model/shipping-service'], 'setShippingRates');
            mocks['Magento_Checkout/js/model/quote'].shippingAddress({
                id: 4,
                getType: function () {
                    return 'address_type_test';
                }
            });
            expect(mocks['Magento_Checkout/js/model/shipping-service'].setShippingRates)
                .not.toHaveBeenCalledWith(rates);
            expect(mocks['Magento_Checkout/js/model/cart/cache'].set).not.toHaveBeenCalled();
            expect(mocks['Magento_Checkout/js/model/shipping-rate-processor/new-address'].getRates).toHaveBeenCalled();
        });

        it('test subscribe when shipping method was changed', function () {
            mocks['Magento_Checkout/js/model/quote'].shippingMethod('flatrate');
            expect(mocks['Magento_Checkout/js/model/cart/totals-processor/default'].estimateTotals).toHaveBeenCalled();
        });

        it('test subscribe when billingAddress was changed for not virtual quote', function () {
            spyOn(mocks['Magento_Checkout/js/model/quote'], 'isVirtual').and.returnValue(true);
            mocks['Magento_Checkout/js/model/quote'].billingAddress({
                id: 6,
                getType: function () {
                    return 'address_type_test';
                }
            });
            expect(mocks['Magento_Checkout/js/model/cart/totals-processor/default'].estimateTotals).toHaveBeenCalled();
        });
    });
});
