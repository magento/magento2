/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'squire',
    'jquery',
    'ko'
], function (Squire, $, ko) {
    'use strict';

    var injector = new Squire(),
        mixin,
        serviceUrl = 'rest/V1/guest-carts/estimate-shipping-methods',
        mocks = {
            'mage/storage': {
                post: function () {} // jscs:ignore jsDoc
            },
            'Magento_Customer/js/customer-data': {
                get: jasmine.createSpy().and.returnValue(
                    ko.observable({
                        'data_id': 1
                    })
                )
            },
            'Magento_Checkout/js/model/url-builder': {
                createUrl: jasmine.createSpy().and.returnValue(serviceUrl)
            }
        };

    describe('Magento_Checkout/js/model/shipping-rate-processor/new-address', function () {
        beforeEach(function (done) {
            window.checkoutConfig = {
                'quoteData': {
                    'is_persistent': '0'
                }
            };

            injector.mock(mocks);
            injector.require(['Magento_Checkout/js/model/shipping-rate-processor/new-address'], function (Mixin) {
                mixin = Mixin;
                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {}

            delete window.checkoutConfig.quoteData.is_persistent;
        });

        it('Check that estimate-shipping-methods API is called synchronously for persistent cart', function () {
            var deferral = new $.Deferred();

            window.checkoutConfig.quoteData.is_persistent = '1';
            spyOn(mocks['mage/storage'], 'post').and.callFake(function () {
                return deferral.resolve({});
            });

            mixin.getRates({
                /** Stub */
                'getCacheKey': function () {
                    return false;
                }
            });

            expect(mocks['mage/storage'].post).toHaveBeenCalledWith(
                serviceUrl,
                '{"address":{}}',
                false,
                'application/json',
                {},
                false
            );
        });

        it('Check that estimate-shipping-methods API is called asynchronously', function () {
            var deferral = new $.Deferred();

            spyOn(mocks['mage/storage'], 'post').and.callFake(function () {
                return deferral.resolve({});
            });

            mixin.getRates({
                /** Stub */
                'getCacheKey': function () {
                    return false;
                }
            });

            expect(mocks['mage/storage'].post).toHaveBeenCalledWith(
                serviceUrl,
                '{"address":{}}',
                false,
                'application/json',
                {},
                true
            );
        });
    });
});
