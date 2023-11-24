/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'squire',
    'ko',
    'mage/translate'
], function ($, Squire, ko, $t) {
    'use strict';

    window.$t = $t;

    describe('paypal/js/view/payment/method-renderer/paypal-express-abstract', function () {
        var injector = new Squire(),
            successPromise = jasmine.createSpyObj('successPromise', ['done']),
            setPaymentMock = jasmine.createSpy('set-payment-information', function () {
                return successPromise;
            }).and.callThrough(),
            validateMock = jasmine.createSpy('validate', function () {
                return true;
            }).and.callThrough(),
            mocks = {
                'Magento_Checkout/js/model/checkout-data-resolver': {

                    /** Stub */
                    applyBillingAddress: function () {
                        return true;
                    },

                    /** Stub */
                    resolveBillingAddress: function () {
                        return true;
                    }
                },
                'Magento_Checkout/js/model/quote': {
                    billingAddress: ko.observable(),
                    shippingAddress: ko.observable(),
                    paymentMethod: ko.observable(),
                    totals: ko.observable({}),

                    /** Stub */
                    isVirtual: function () {
                        return false;
                    }
                },
                'Magento_Checkout/js/action/set-payment-information': setPaymentMock,
                'Magento_Checkout/js/model/payment/additional-validators': {
                    validate: validateMock
                }
            },
            paypalExpressAbstract,
            tplElement = $('<div data-bind="with: child"><div data-bind="template: getTemplate()"></div></div>')[0];

        /**
         * Click on PayPal help link and call expectation
         * @param {Function} expectation
         */
        function clickOnHelpLink(expectation) {
            $('div.payment-method-title.field.choice > label > a > span').trigger('click');
            expectation();
        }

        beforeAll(function (done) {
            window.checkoutConfig = {
                quoteData: {}
            };
            injector.mock(mocks);
            injector.require(
                ['Magento_Paypal/js/view/payment/method-renderer/paypal-express-abstract'],
                function (Constr) {
                    paypalExpressAbstract = new Constr({
                        isChecked: ko.observable(true),
                        provider: 'provName',
                        name: 'test',
                        index: 'test',
                        item: {
                            method: 'payflow_express_bml'
                        }
                    });

                    paypalExpressAbstract.child = paypalExpressAbstract;
                    $(document.body).append(tplElement);
                    ko.applyBindings(paypalExpressAbstract, tplElement);
                    done();
                });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {}
        });

        it('showAcceptanceWindow is invoked when the anchor element of help link is clicked', function (done) {
            spyOn(paypalExpressAbstract, 'showAcceptanceWindow');
            setTimeout(function () {
                clickOnHelpLink(function () {
                    expect(paypalExpressAbstract.showAcceptanceWindow).toHaveBeenCalled();
                });
                done();
            }, 500);
        });

        it('Help link should be available in showAcceptanceWindow function', function (done) {
            spyOn(window, 'open');
            setTimeout(function () {
                clickOnHelpLink(function () {
                    expect(window.open).toHaveBeenCalledWith(
                        jasmine.stringMatching('http'),
                        jasmine.anything(),
                        jasmine.anything()
                    );
                });
                done();
            }, 500);
        });

        it('setPaymentMethodAction is called before redirect to paypal', function () {
            paypalExpressAbstract.continueToPayPal();
            expect(validateMock).toHaveBeenCalled();
            expect(validateMock.calls.mostRecent()).toEqual(jasmine.objectContaining({
                object: mocks['Magento_Checkout/js/model/payment/additional-validators'],
                args: [],
                returnValue: true
            }));
            expect(setPaymentMock).toHaveBeenCalled();
            expect(setPaymentMock.calls.mostRecent()).toEqual(jasmine.objectContaining({
                returnValue: successPromise
            }));
            expect(successPromise.done).toHaveBeenCalledWith(jasmine.any(Function));
        });

        afterAll(function (done) {
            tplElement.remove();
            done();
        });
    });
});
