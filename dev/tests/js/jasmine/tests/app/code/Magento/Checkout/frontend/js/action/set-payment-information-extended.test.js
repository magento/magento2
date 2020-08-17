/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'squire',
    'jquery'
], function (Squire, $) {
    'use strict';

    var injector = new Squire(),
        setPaymentInformation,
        serviceUrl = 'http://url',
        mocks = {
            'Magento_Checkout/js/model/quote': {
                getQuoteId: jasmine.createSpy().and.returnValue(1),
                billingAddress: jasmine.createSpy().and.returnValue(null)
            },
            'Magento_Checkout/js/model/url-builder': {
                createUrl: jasmine.createSpy().and.returnValue(serviceUrl)
            },
            'mage/storage': {
                post: function () {} // jscs:ignore jsDoc
            },
            'Magento_Customer/js/model/customer': {
                isLoggedIn: jasmine.createSpy().and.returnValue(false)
            },
            'Magento_Checkout/js/model/full-screen-loader': {
                startLoader: jasmine.createSpy(),
                stopLoader: jasmine.createSpy()
            },
            'Magento_Checkout/js/action/get-totals': jasmine.createSpy('getTotalsAction'),
            'Magento_Checkout/js/model/error-processor': jasmine.createSpy('errorProcessor')
        };

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(
            ['Magento_Checkout/js/action/set-payment-information-extended'],
            function (action) {
                setPaymentInformation = action;
                done();
            });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {
        }
    });

    describe('Magento/Checkout/js/action/set-payment-information-extended', function () {
        it('Checks that paymentData consist correct data value.', function () {
            var messageContainer = jasmine.createSpy('messageContainer'),
                deferral = new $.Deferred(),
                paymentData = {
                    method: 'checkmo',
                    additionalData: null,
                    __disableTmpl: {
                        title: true
                    }
                },
                payload = {
                    cartId: 1,
                    paymentMethod: {
                        method: 'checkmo',
                        additionalData: null
                    },
                    billingAddress: null
                };

            spyOn(mocks['mage/storage'], 'post').and.callFake(function () {
                return deferral.resolve({});
            });

            setPaymentInformation(messageContainer, paymentData, false);
            expect(mocks['Magento_Checkout/js/model/full-screen-loader'].startLoader).toHaveBeenCalled();
            expect(mocks['mage/storage'].post).toHaveBeenCalledWith(serviceUrl, JSON.stringify(payload));
            expect(mocks['Magento_Checkout/js/model/full-screen-loader'].stopLoader).toHaveBeenCalled();
        });
    });
});
