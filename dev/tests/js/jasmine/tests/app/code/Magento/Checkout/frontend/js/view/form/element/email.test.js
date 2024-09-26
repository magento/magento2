/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */

define(['squire', 'ko', 'jquery', 'jquery/validate'], function (Squire, ko, $) {
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
                'Magento_Checkout/js/view/shipping': jasmine.createSpy()
            },
            Component;

        beforeEach(function (done) {
            window.checkoutConfig = {};
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

        describe('"validateEmail" method', function () {
            beforeEach(function () {
                $('body').append('<form data-role="email-with-possible-login">' +
                    '<input type="text" name="username" />' +
                    '</form>');
                spyOn($.fn, 'validate').and.returnValue(true);
            });
            it('Check if login form will be validated in case it is not visible', function () {
                var loginFormSelector = 'form[data-role=email-with-possible-login]',
                    loginForm = $(loginFormSelector);

                loginForm.hide();
                Component.validateEmail();
                expect(loginForm.is(':visible')).toBeFalsy();
                expect(loginForm.validate).not.toHaveBeenCalled();
            });
        });
    });
});
