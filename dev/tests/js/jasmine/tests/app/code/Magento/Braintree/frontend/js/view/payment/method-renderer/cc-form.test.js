/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'squire',
    'ko',
    'Magento_Ui/js/model/messages'
], function ($, Squire, ko, Messages) {
    'use strict';

    describe('Magento_Braintree/js/view/payment/method-renderer/cc-form', function () {
        var injector = new Squire(),
            mocks = {
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
                'Magento_Braintree/js/view/payment/validator-handler': jasmine.createSpyObj(
                    'validator-handler',
                    ['initialize']
                ),
                'Magento_Braintree/js/view/payment/adapter':  jasmine.createSpyObj(
                    'adapter',
                    ['setup', 'setConfig', 'showError']
                )
            },
            braintreeCcForm;

        beforeAll(function (done) {
            window.checkoutConfig = {
                quoteData: {},
                payment: {
                    braintree: {
                        hasFraudProtection: true
                    }
                }
            };
            injector.mock(mocks);
            injector.require(['Magento_Braintree/js/view/payment/method-renderer/cc-form'], function (Constr) {
                braintreeCcForm = new Constr({
                    provider: 'provName',
                    name: 'test',
                    index: 'test',
                    item: {
                        title: 'Braintree'
                    }
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

        it('Check if payment code and message container are restored after onActiveChange call.', function () {
            var expectedMessageContainer = braintreeCcForm.messageContainer,
                expectedCode = braintreeCcForm.code;

            braintreeCcForm.code = 'braintree-vault';
            braintreeCcForm.messageContainer = new Messages();

            braintreeCcForm.onActiveChange(true);

            expect(braintreeCcForm.getCode()).toEqual(expectedCode);
            expect(braintreeCcForm.messageContainer).toEqual(expectedMessageContainer);
        });

        it('Check if form validation fails when "Place Order" button should be active.', function () {
            var errorMessage = 'Something went wrong.',

                /**
                 * Anonymous wrapper
                 */
                func = function () {
                    braintreeCcForm.clientConfig.onError({
                        'message': errorMessage
                    });
                };

            expect(func).toThrow(errorMessage);
            expect(braintreeCcForm.isPlaceOrderActionAllowed()).toBeTruthy();
        });
    });
});
