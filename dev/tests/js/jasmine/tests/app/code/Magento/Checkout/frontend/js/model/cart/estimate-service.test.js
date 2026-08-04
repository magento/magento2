/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

/*jscs:disable jsDoc*/
require.config({
    map: {
        '*': {
            'Magento_Checkout/js/model/shipping-service': 'Magento_Checkout/js/model/shipping-service'
        }
    }
});

define([
    'squire',
    'ko'
], function (Squire, ko) {
    'use strict';

    var injector = new Squire(),
        rates = [
            {'carrier_code': 'flatrate', 'method_code': 'flatrate'}
        ],
        mocks = {
            'Magento_Checkout/js/model/quote': {
                shippingAddress: ko.observable(),
                isVirtual: function () {return false;},
                billingAddress: ko.observable(),
                shippingMethod: ko.observable()
            },
            'Magento_Checkout/js/model/shipping-rate-processor/new-address': {
                getRates: function () {}
            },
            'Magento_Checkout/js/model/cart/totals-processor/default': {
                estimateTotals: function () {}
            },
            'Magento_Checkout/js/model/shipping-service': {
                ratesObservable: ko.observableArray([]),
                setShippingRates: function (data) {this.ratesObservable(data);},
                isLoading: ko.observable(),
                getShippingRates: function () {return this.ratesObservable;}
            },
            'Magento_Checkout/js/model/cart/cache': {
                isChanged: function () {return false;},
                get: function () {return null;},
                set: function () {}
            },
            'Magento_Customer/js/customer-data': {
                get: function () {return ko.observable({'data_id': 1});}
            }
        };

    beforeAll(function (done) {
        window.checkoutConfig = {
            quoteData: {},
            storeCode: 'US'
        };
        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/model/cart/estimate-service'], function () {
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
            // eslint-disable-next-line no-unused-vars
        } catch (e) {
        }
    });

    describe('Magento_Checkout/js/model/cart/estimate-service', function () {

        it('test subscribe when billingAddress was changed for not virtual quote', function () {
            spyOn(mocks['Magento_Checkout/js/model/quote'], 'isVirtual').and.returnValue(false);
            spyOn(mocks['Magento_Checkout/js/model/cart/totals-processor/default'], 'estimateTotals');
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
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'isChanged').and.returnValues(false, false);
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'get').and.returnValue(rates);
            spyOn(mocks['Magento_Checkout/js/model/shipping-service'], 'setShippingRates');
            spyOn(mocks['Magento_Checkout/js/model/cart/totals-processor/default'], 'estimateTotals');
            spyOn(mocks['Magento_Checkout/js/model/shipping-rate-processor/new-address'], 'getRates');
            mocks['Magento_Checkout/js/model/quote'].shippingAddress({
                id: 2,
                getType: function () {
                    return 'address_type_test';
                }
            });
            expect(mocks['Magento_Checkout/js/model/shipping-service'].setShippingRates).toHaveBeenCalledWith(rates);
            expect(mocks['Magento_Checkout/js/model/cart/totals-processor/default'].estimateTotals)
                .not.toHaveBeenCalled();
            expect(mocks['Magento_Checkout/js/model/shipping-rate-processor/new-address'].getRates)
                .not.toHaveBeenCalled();
        });

        it('test subscribe when shipping address was changed for virtual quote', function () {
            spyOn(mocks['Magento_Checkout/js/model/quote'], 'isVirtual').and.returnValue(true);
            spyOn(mocks['Magento_Checkout/js/model/cart/totals-processor/default'], 'estimateTotals');
            spyOn(mocks['Magento_Checkout/js/model/shipping-rate-processor/new-address'], 'getRates');
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
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'isChanged').and.returnValue(true);
            spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'set');
            spyOn(mocks['Magento_Checkout/js/model/shipping-service'], 'setShippingRates');
            spyOn(mocks['Magento_Checkout/js/model/cart/totals-processor/default'], 'estimateTotals');
            spyOn(mocks['Magento_Checkout/js/model/shipping-rate-processor/new-address'], 'getRates');
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
            expect(mocks['Magento_Checkout/js/model/cart/totals-processor/default'].estimateTotals)
                .not.toHaveBeenCalled();
        });

        it('test subscribe when billingAddress was changed for virtual quote', function () {
            spyOn(mocks['Magento_Checkout/js/model/quote'], 'isVirtual').and.returnValue(true);
            spyOn(mocks['Magento_Checkout/js/model/cart/totals-processor/default'], 'estimateTotals');
            mocks['Magento_Checkout/js/model/quote'].billingAddress({
                id: 6,
                getType: function () {
                    return 'address_type_test';
                }
            });
            expect(mocks['Magento_Checkout/js/model/cart/totals-processor/default'].estimateTotals)
                .toHaveBeenCalledTimes(1);
        });

        it('test estimateTotals is triggered exactly once when shipping method changes', function (done) {
            spyOn(mocks['Magento_Checkout/js/model/cart/totals-processor/default'], 'estimateTotals');
            mocks['Magento_Checkout/js/model/quote'].shippingMethod(rates[0]);
            setTimeout(function () {
                expect(mocks['Magento_Checkout/js/model/cart/totals-processor/default'].estimateTotals)
                    .toHaveBeenCalledTimes(1);
                done();
            }, 51);
        });

        it(
            'test estimateTotals is triggered exactly once and rates cache is updated when shipping rates changes',
            function (done) {
                var newRates = [
                    {'carrier_code': 'flatrate', 'method_code': 'flatrate'},
                    {'carrier_code': 'freeshipping', 'method_code': 'freeshipping'}
                ];

                spyOn(mocks['Magento_Checkout/js/model/cart/totals-processor/default'], 'estimateTotals');
                spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'set');
                mocks['Magento_Checkout/js/model/shipping-service'].setShippingRates(newRates);
                // change shipping method to trigger second request for estimateTotals
                mocks['Magento_Checkout/js/model/quote'].shippingMethod(rates[0]);
                setTimeout(function () {
                    expect(mocks['Magento_Checkout/js/model/cart/cache'].set)
                        .toHaveBeenCalledWith('rates', newRates);
                    expect(mocks['Magento_Checkout/js/model/cart/totals-processor/default'].estimateTotals)
                        .toHaveBeenCalledTimes(1);
                    done();
                }, 51);
            }
        );

        it(
            'test estimateTotals is triggered exactly once when shipping rates changes come from cache',
            function (done) {
                var newRates = [
                    {'carrier_code': 'flatrate', 'method_code': 'flatrate'},
                    {'carrier_code': 'freeshipping', 'method_code': 'freeshipping'}
                ];

                spyOn(mocks['Magento_Checkout/js/model/cart/totals-processor/default'], 'estimateTotals');
                spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'get').and.returnValue(newRates);
                spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'set');
                mocks['Magento_Checkout/js/model/shipping-service'].setShippingRates(newRates);
                // change shipping method to trigger second request for estimateTotals
                mocks['Magento_Checkout/js/model/quote'].shippingMethod(rates[0]);
                setTimeout(function () {
                    expect(mocks['Magento_Checkout/js/model/cart/cache'].set).not.toHaveBeenCalled();
                    expect(mocks['Magento_Checkout/js/model/cart/totals-processor/default'].estimateTotals)
                        .toHaveBeenCalledTimes(1);
                    done();
                }, 51);
            }
        );

        it(
            'test estimateTotals is not triggered when shipping rates changes come from cache' +
            ' and shipping method is not changed',
            function (done) {
                var newRates = [
                    {'carrier_code': 'flatrate', 'method_code': 'flatrate'},
                    {'carrier_code': 'freeshipping', 'method_code': 'freeshipping'}
                ];

                spyOn(mocks['Magento_Checkout/js/model/cart/totals-processor/default'], 'estimateTotals');
                spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'get').and.returnValue(newRates);
                spyOn(mocks['Magento_Checkout/js/model/cart/cache'], 'set');
                mocks['Magento_Checkout/js/model/shipping-service'].setShippingRates(newRates);
                setTimeout(function () {
                    expect(mocks['Magento_Checkout/js/model/cart/cache'].set).not.toHaveBeenCalled();
                    expect(mocks['Magento_Checkout/js/model/cart/totals-processor/default'].estimateTotals)
                        .not.toHaveBeenCalled();
                    done();
                }, 51);
            }
        );
    });
});
