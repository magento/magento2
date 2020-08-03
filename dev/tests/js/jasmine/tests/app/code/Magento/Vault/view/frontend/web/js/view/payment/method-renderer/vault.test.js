/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'squire',
    'ko'
], function (Squire, ko) {
    'use strict';

    var injector = new Squire(),
        vault,
        mocks = {
            'Magento_Checkout/js/model/checkout-data-resolver': {
                resolveBillingAddress: jasmine.createSpy().and.returnValue(true)
            },
            'Magento_Checkout/js/checkout-data': {
                setSelectedPaymentMethod: jasmine.createSpy().and.returnValue(false)
            },
            'Magento_Checkout/js/model/quote': {
                billingAddress: ko.observable(null),
                shippingAddress: ko.observable(null),
                paymentMethod: ko.observable(null),
                totals: ko.observable({})
            }
        },
        billingAddress = {
            city: 'Culver City',
            company: 'Magento',
            country_id: 'US',// jscs:ignore requireCamelCaseOrUpperCaseIdentifiers
            firstname: 'John',
            lastname: 'Doe',
            postcode: '90230',
            region: '',
            region_id: '12',// jscs:ignore requireCamelCaseOrUpperCaseIdentifiers
            street: {
                0: '6161 West Centinela Avenue',
                1: ''
            },
            telephone: '+15555555555'
        };

    beforeEach(function (done) {
        window.checkoutConfig = {
            quoteData: {
                /* jscs:disable requireCamelCaseOrUpperCaseIdentifiers */
                entity_Id: 1
            },
            formKey: 'formKey'
        };
        injector.mock(mocks);
        injector.require(['Magento_Vault/js/view/payment/method-renderer/vault'], function (Constr) {
            var params = {
                index: 'vaultIndex',
                item: {
                    method: 'vault'
                }
            };

            vault = new Constr(params);
            // eslint-disable-next-line max-nested-callbacks
            /** Stub */
            vault.isChecked = function () {
                return mocks['Magento_Checkout/js/model/quote'].paymentMethod() ?
                    mocks['Magento_Checkout/js/model/quote'].paymentMethod().method : null;
            };
            done();
        });
    });

    afterEach(function () {
        try {
            injector.remove();
            injector.clean();
        } catch (e) {
        }
        mocks['Magento_Checkout/js/model/quote'].billingAddress(null);
        mocks['Magento_Checkout/js/model/quote'].paymentMethod(null);
    });

    describe('Magento_Vault/js/view/payment/method-renderer/vault', function () {

        it('There is no payment method and billing address', function () {
            expect(vault.isButtonActive()).toBeFalsy();

            expect(vault.isActive()).toBeFalsy();
            expect(vault.isPlaceOrderActionAllowed()).toBeFalsy();
        });

        it('Payment method exists, but place order action is not allowed', function () {
            vault.selectPaymentMethod();
            expect(mocks['Magento_Checkout/js/model/quote'].paymentMethod().method).toEqual('vaultIndex');

            expect(vault.isButtonActive()).toBeFalsy();

            expect(vault.isActive()).toBeTruthy();
            expect(vault.isPlaceOrderActionAllowed()).toBeFalsy();

        });

        it('Billing address exists, but there is no selected payment method', function () {
            mocks['Magento_Checkout/js/model/quote'].billingAddress(billingAddress);

            expect(vault.isButtonActive()).toBeFalsy();

            expect(vault.isActive()).toBeFalsy();
            expect(vault.isPlaceOrderActionAllowed).toBeTruthy();
        });

        it('Button is active', function () {
            vault.selectPaymentMethod();
            expect(mocks['Magento_Checkout/js/model/quote'].paymentMethod().method).toEqual('vaultIndex');

            mocks['Magento_Checkout/js/model/quote'].billingAddress(billingAddress);

            expect(vault.isButtonActive()).toBeTruthy();

            expect(vault.isActive()).toBeTruthy();
            expect(vault.isPlaceOrderActionAllowed()).toBeTruthy();
        });
    });
});
