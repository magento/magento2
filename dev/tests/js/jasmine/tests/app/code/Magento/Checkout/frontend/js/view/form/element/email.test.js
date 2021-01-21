/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */

define(['squire', 'ko'], function (Squire, ko) {
    'use strict';

    describe('Magento_Checkout/js/view/form/element/email', function () {
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
            Component;

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require(['Magento_Checkout/js/view/form/element/email'], function (Constr) {
                Component = new Constr({
                    provider: 'provName',
                    name: '',
                    index: '',
                    isPasswordVisible: false,
                    isEmailCheckComplete: null
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

        describe('"resolveInitialPasswordVisibility" method', function () {
            it('Check return type of method.', function () {
                expect(typeof Component.resolveInitialPasswordVisibility()).toEqual('boolean');
            });
        });
    });
});
