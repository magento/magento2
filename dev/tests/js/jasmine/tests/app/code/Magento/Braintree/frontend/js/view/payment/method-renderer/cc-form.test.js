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
                    totals: ko.observable({})
                },
                'Magento_Braintree/js/view/payment/validator-handler': jasmine.createSpyObj(
                    'validator-handler',
                    ['initialize']
                ),
                'Magento_Braintree/js/view/payment/adapter':  jasmine.createSpyObj(
                    'adapter',
                    ['setup', 'setConfig']
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
                        index: 'test'
                    });

                    done();
                });
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
    });
});
