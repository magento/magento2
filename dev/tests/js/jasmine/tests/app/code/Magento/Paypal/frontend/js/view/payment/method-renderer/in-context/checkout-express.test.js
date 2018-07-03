/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire',
    'ko',
    'jquery',
    'uiComponent'
], function (Squire, ko, $, Component) {
    'use strict';

    describe('paypal/js/view/payment/method-renderer/paypal-express-abstract', function () {
        var injector = new Squire(),
            mocks = {
                'Magento_Paypal/js/action/set-payment-method': jasmine.createSpy(),
                'Magento_Paypal/js/view/payment/method-renderer/paypal-express-abstract': Component,
                'Magento_Checkout/js/model/quote': {
                    billingAddress: ko.observable(),
                    shippingAddress: ko.observable(),
                    paymentMethod: ko.observable()
                },
                'Magento_Checkout/js/model/payment/additional-validators': {
                    validate: jasmine.createSpy().and.returnValue(true)
                },
                'Magento_Ui/js/model/messageList': {
                    addErrorMessage: jasmine.createSpy(),
                    addSuccessMessage: jasmine.createSpy()
                },
                'paypalInContextExpressCheckout': {
                    checkout: {
                        initXO: jasmine.createSpy(),
                        closeFlow: jasmine.createSpy(),
                        startFlow: jasmine.createSpy()
                    }
                },
                'Magento_Customer/js/customer-data': {
                    invalidate: jasmine.createSpy()
                }
            },
            obj;

        beforeAll(function (done) {
            injector.mock(mocks);
            injector.require(
                ['Magento_Paypal/js/view/payment/method-renderer/in-context/checkout-express'],
                function (Constr) {
                    obj = new Constr({
                        provider: 'provName',
                        name: 'test',
                        index: 'test',
                        selectPaymentMethod: jasmine.createSpy()
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

        describe('"click" method checks', function () {
            it('check success request', function () {
                mocks['Magento_Paypal/js/action/set-payment-method'].and.callFake(function () {
                    var promise = $.Deferred();

                    promise.resolve();

                    return promise;
                });
                spyOn(jQuery, 'get').and.callFake(function () {
                    var promise = $.Deferred();

                    promise.resolve({
                        url: 'url'
                    });

                    return promise;
                });

                obj.clientConfig.click(new Event('event'));

                expect(mocks.paypalInContextExpressCheckout.checkout.initXO).toHaveBeenCalled();
                expect(mocks.paypalInContextExpressCheckout.checkout.startFlow).toHaveBeenCalledWith('url');
                expect(mocks.paypalInContextExpressCheckout.checkout.closeFlow).not.toHaveBeenCalled();
                expect(mocks['Magento_Customer/js/customer-data'].invalidate).toHaveBeenCalled();
            });

            it('check request with error message', function () {
                mocks['Magento_Paypal/js/action/set-payment-method'].and.callFake(function () {
                    var promise = $.Deferred();

                    promise.resolve();

                    return promise;
                });
                spyOn(jQuery, 'get').and.callFake(function () {
                    var promise = $.Deferred();

                    promise.resolve({
                        message: {
                            text: 'Text',
                            type: 'error'
                        }
                    });

                    return promise;
                });

                obj.clientConfig.click(new Event('event'));

                expect(mocks['Magento_Ui/js/model/messageList'].addErrorMessage).toHaveBeenCalledWith({
                    message: 'Text'
                });
                expect(mocks.paypalInContextExpressCheckout.checkout.initXO).toHaveBeenCalled();
                expect(mocks.paypalInContextExpressCheckout.checkout.closeFlow).toHaveBeenCalled();
                expect(mocks['Magento_Customer/js/customer-data'].invalidate).toHaveBeenCalled();
            });

            it('check on fail request', function () {
                mocks['Magento_Paypal/js/action/set-payment-method'].and.callFake(function () {
                    var promise = $.Deferred();

                    promise.reject();

                    return promise;
                });
                spyOn(jQuery, 'get');

                obj.clientConfig.click(new Event('event'));

                expect(jQuery.get).not.toHaveBeenCalled();
            });
        });
    });
});
