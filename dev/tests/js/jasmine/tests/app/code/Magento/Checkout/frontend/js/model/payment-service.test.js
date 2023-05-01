/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'squire',
    'ko'
], function (Squire, ko) {
    'use strict';

    let injector = new Squire(),
        paymentService,
        methods = [
            {title: 'Credit Card', method: 'credit_card'},
            {title: 'Stored Cards', method: 'credit_card_vault'}
        ],
        mocksPaymentMethodCheckmo = {
            'Magento_Checkout/js/model/quote': {
                paymentMethod: ko.observable({
                    'method': 'checkmo'
                })
            }
        },
        mocksPaymentMethodVault = {
            'Magento_Checkout/js/model/quote': {
                paymentMethod: ko.observable({
                    'method': 'credit_card_vault_1'
                })
            }
        };

    beforeEach(function (done) {
        window.checkoutConfig = {
            vault: {
                credit_card_vault: {}
            },
            payment: {
                vault: {
                    credit_card_vault_1: {},
                    credit_card_vault_2: {}
                }
            }
        };
        done();
    });

    afterEach(function () {
        try {
            injector.remove();
            injector.clean();
        } catch (e) {}
    });

    describe('Magento_Checkout/js/model/payment-service', function () {
        beforeEach(function (done) {
            injector.mock(mocksPaymentMethodCheckmo);
            // eslint-disable-next-line max-nested-callbacks
            injector.require(['Magento_Checkout/js/model/payment-service'], function (instance) {
                paymentService = instance;
                done();
            });
        });
        it('payment method is not enabled', function () {
            paymentService.setPaymentMethods(methods);
            expect(mocksPaymentMethodCheckmo['Magento_Checkout/js/model/quote'].paymentMethod()).toBeNull();
        });
    });

    describe('Magento_Checkout/js/model/payment-service', function () {
        beforeEach(function (done) {
            injector.mock(mocksPaymentMethodVault);
            // eslint-disable-next-line max-nested-callbacks
            injector.require(['Magento_Checkout/js/model/payment-service'], function (instance) {
                paymentService = instance;
                done();
            });
        });
        it('payment method is stored credit card', function () {
            paymentService.setPaymentMethods(methods);
            expect(mocksPaymentMethodVault['Magento_Checkout/js/model/quote'].paymentMethod().method)
                .toEqual('credit_card_vault_1');
        });
    });
});
