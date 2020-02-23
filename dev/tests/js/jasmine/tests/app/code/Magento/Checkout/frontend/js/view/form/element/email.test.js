/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */

define(['squire', 'ko'], function (Squire, ko) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Customer/js/model/customer': {
                isLoggedIn: ko.observable()
            },
            'Magento_Customer/js/action/check-email-availability': jasmine.createSpy(),
            'Magento_Customer/js/action/login': jasmine.createSpy(),
            'Magento_Checkout/js/model/quote': {
                isVirtual: jasmine.createSpy(),
                shippingAddress: jasmine.createSpy()
            },
            'Magento_Checkout/js/checkout-data': jasmine.createSpyObj(
                'checkoutData',
                [
                    'setInputFieldEmailValue',
                    'setValidatedEmailValue',
                    'setCheckedEmailValue',
                    'getInputFieldEmailValue',
                    'getValidatedEmailValue',
                    'getCheckedEmailValue'
                ]
            ),
            'Magento_Checkout/js/model/full-screen-loader': jasmine.createSpy(),
            'mage/validation': jasmine.createSpy()
        },
        obj;

    beforeEach(function (done) {
        window.checkoutConfig = {
            quoteData: {
                /* jscs:disable requireCamelCaseOrUpperCaseIdentifiers */
                entity_Id: 1
            },
            validatedEmailValue: 'test@gmail.com',
            formKey: 'formKey'
        };

        injector.mock(mocks);
        injector.require(['Magento_Checkout/js/view/form/element/email'], function (Constr) {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: ''
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

    describe('Magento_Checkout/js/view/form/element/email', function () {
        describe('"setInputFieldEmailValue" method', function () {
            it('Check method setInputFieldEmailValue called by checkoutData.', function () {
                expect(mocks['Magento_Checkout/js/checkout-data'].setInputFieldEmailValue)
                    .toHaveBeenCalledWith(window.checkoutConfig.validatedEmailValue);
            });
        });

        describe('"initObservable" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initObservable')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.initialize;

                expect(type).toEqual('function');
            });
        });

        describe('"initConfig" method', function () {
            it('Check for return type of current method.', function () {
                expect(obj.initConfig()).toEqual(obj);
            });

            it('Check isPasswordVisible variable type', function () {
                expect(typeof obj.isPasswordVisible()).toEqual('boolean');
            });
        });

        describe('"resolveInitialPasswordVisibility" method', function () {
            it('Check return type of method.', function () {
                expect(typeof obj.resolveInitialPasswordVisibility()).toEqual('boolean');
            });
        });
    });
});
