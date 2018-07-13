/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire',
    'ko'
], function (Squire, ko) {
    'use strict';

    describe('Magento_Braintree/js/view/payment/method-renderer/paypal', function () {

        var injector = new Squire(),
            mocks = {
                'Magento_Checkout/js/model/quote': {
                    billingAddress: ko.observable(),
                    shippingAddress: ko.observable({
                        postcode: '',
                        street: [],
                        canUseForBilling: ko.observable()
                    }),
                    paymentMethod: ko.observable(),
                    totals: ko.observable({
                        'base_grand_total': 0
                    })
                },
                'Magento_Braintree/js/view/payment/adapter': {
                    checkout: {
                        paypal: {
                            /** Stub */
                            initAuthFlow: function () {}
                        }
                    }
                }
            },
            braintreeAdapter,
            component,
            additionalValidator;

        beforeEach(function (done) {
            window.checkoutConfig = {
                quoteData: {},
                payment: {
                    'braintree_paypal': {
                        title: 'Braintree PayPal'
                    }
                },
                vault: {}
            };

            injector.mock(mocks);

            injector.require([
                'Magento_Braintree/js/view/payment/adapter',
                'Magento_Checkout/js/model/payment/additional-validators',
                'Magento_Braintree/js/view/payment/method-renderer/paypal'
            ], function (adapter, validator, Constr) {
                braintreeAdapter = adapter;
                additionalValidator = validator;
                component = new Constr();
                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {}
        });

        it('The PayPal::initAuthFlow throws an exception.', function () {

            spyOn(additionalValidator, 'validate').and.returnValue(true);
            spyOn(braintreeAdapter.checkout.paypal, 'initAuthFlow').and.callFake(function () {
                throw new TypeError('Cannot read property of undefined');
            });
            spyOn(component.messageContainer, 'addErrorMessage');

            component.payWithPayPal();
            expect(component.messageContainer.addErrorMessage).toHaveBeenCalled();
        });
    });
});
