/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire',
    'ko',
    'jquery',
    'uiComponent'
], function (Squire, ko, $, Component) {
    'use strict';

    describe('paypal/js/view/payment/method-renderer/paypal-express-in-context', function () {
        var injector = new Squire(),
            setPaymentMethodSpy = jasmine.createSpy(),
            mocks = {
                'Magento_Paypal/js/action/set-payment-method': setPaymentMethodSpy,
                'Magento_Paypal/js/view/payment/method-renderer/paypal-express-abstract': Component,
                'Magento_Checkout/js/model/quote': {
                    billingAddress: ko.observable(),
                    shippingAddress: ko.observable(),
                    paymentMethod: ko.observable()
                },
                'Magento_Checkout/js/model/payment/additional-validators': {
                    validate: jasmine.createSpy().and.returnValue(true)
                },
                'Magento_Paypal/js/in-context/express-checkout-smart-buttons': jasmine.createSpy(),
                'Magento_Customer/js/customer-data': {
                    invalidate: jasmine.createSpy()
                }
            },
            obj;

        beforeAll(function (done) {
            window.checkoutConfig = {
                quoteData: {
                    /* jscs:disable requireCamelCaseOrUpperCaseIdentifiers */
                    entity_id: 1
                },
                formKey: 'formKey'
            };
            window.customerData = {
                id: 1
            };
            injector.mock(mocks);
            injector.require(
                ['Magento_Paypal/js/view/payment/method-renderer/in-context/checkout-express'],
                function (Constr) {
                    obj = new Constr({
                        provider: 'provName',
                        name: 'test',
                        index: 'test',
                        item: {
                            method: 'payflow_express_bml'
                        },
                        clientConfig: {}
                    });
                    done();
                });
        });
        /*eslint-disable no-unused-vars*/
        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {}
        });

        describe('check smart button initialization', function () {
            it('express-checkout-smart-buttons is initialized', function () {

                obj.renderPayPalButtons();
                expect(mocks['Magento_Paypal/js/in-context/express-checkout-smart-buttons']).toHaveBeenCalled();
            });
        });

        describe('beforePayment', function () {
            var reject;

            beforeEach(function () {
                reject = jasmine.createSpy();
                spyOn(obj, 'addError');
            });

            /**
             * @param {String} responseText
             */
            function failWith(responseText) {
                var deferred = $.Deferred();

                setPaymentMethodSpy.and.returnValue(deferred.promise());
                obj.beforePayment(jasmine.createSpy(), reject);
                deferred.reject({
                    responseText: responseText
                });
            }

            it('adds the error message string from a JSON response', function () {
                failWith('{"message":"Enter a valid address."}');

                expect(obj.addError).toHaveBeenCalledWith('Enter a valid address.');
                expect(reject.calls.mostRecent().args[0].message).toBe('Enter a valid address.');
            });

            it('falls back to the default message when the response is not JSON', function () {
                failWith('<html>error</html>');

                expect(obj.addError).toHaveBeenCalledWith(obj.paymentActionError);
            });

            it('falls back to the default message when the response has no message', function () {
                failWith('{"parameters":[]}');

                expect(obj.addError).toHaveBeenCalledWith(obj.paymentActionError);
            });
        });
    });
});
