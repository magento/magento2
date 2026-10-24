/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
                'Magento_Checkout/js/model/full-screen-loader': jasmine.createSpyObj(
                    'fullScreenLoader', ['startLoader', 'stopLoader']
                ),
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


        describe('login submissions', function () {
            var request,
                form;

            beforeEach(function () {
                request = $.Deferred();
                form = $('<form><input name="username" value="customer@example.com"/></form>');
                spyOn($.fn, 'validation').and.returnValue(true);
                mocks['Magento_Customer/js/action/login'].calls.reset();
                mocks['Magento_Customer/js/action/login'].and.returnValue(request.promise());
                Component.isPasswordVisible(true);
            });

            it('ignores repeated submissions while pending', function () {
                Component.login(form[0]);
                Component.login(form[0]);

                expect(mocks['Magento_Customer/js/action/login'].calls.count()).toBe(1);
            });

            it('allows retry after credential errors', function () {
                Component.login(form[0]);
                request.resolve({errors: true});
                Component.login(form[0]);

                expect(mocks['Magento_Customer/js/action/login'].calls.count()).toBe(2);
            });

            it('allows retry after a transport failure', function () {
                Component.login(form[0]);
                request.reject();
                Component.login(form[0]);

                expect(mocks['Magento_Customer/js/action/login'].calls.count()).toBe(2);
            });

            it('ignores submissions after success while navigation is pending', function () {
                Component.login(form[0]);
                request.resolve({errors: false});
                Component.login(form[0]);

                expect(mocks['Magento_Customer/js/action/login'].calls.count()).toBe(1);
            });
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
