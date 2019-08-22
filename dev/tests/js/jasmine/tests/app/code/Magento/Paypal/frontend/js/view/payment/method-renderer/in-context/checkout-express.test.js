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
                'Magento_Paypal/js/in-context/express-checkout-smart-buttons': jasmine.createSpy(),
                'Magento_Customer/js/customer-data': {
                    invalidate: jasmine.createSpy()
                }
            },
            obj;

        beforeAll(function (done) {
            window.checkoutConfig = {
                quoteData: {
                    entityId: 1
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
    });
});
