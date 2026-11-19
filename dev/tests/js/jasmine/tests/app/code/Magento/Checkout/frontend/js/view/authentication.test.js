/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/* eslint max-nested-callbacks: 0 */
define(['squire', 'jquery'], function (Squire, $) {
    'use strict';

    describe('Magento_Checkout/js/view/authentication', function () {
        var injector,
            Component,
            loginAction,
            mocks;

        beforeEach(function (done) {
            window.checkoutConfig = {};
            injector = new Squire();
            loginAction = jasmine.createSpy();
            mocks = {
                'Magento_Customer/js/action/login': loginAction,
                'Magento_Customer/js/model/customer': {isLoggedIn: jasmine.createSpy()},
                'Magento_Checkout/js/model/authentication-messages': {},
                'Magento_Checkout/js/model/full-screen-loader': jasmine.createSpyObj(
                    'fullScreenLoader', ['startLoader', 'stopLoader']
                )
            };
            injector.mock(mocks);
            injector.require(['Magento_Checkout/js/view/authentication'], function (Constr) {
                Component = new Constr({provider: 'provName', name: '', index: ''});
                done();
            });
        });

        afterEach(function () {
            injector.clean();
            injector.remove();
        });

        describe('login submissions', function () {
            var request,
                form;

            beforeEach(function () {
                request = $.Deferred();
                form = $('<form><input name="username" value="customer@example.com"/></form>');
                spyOn($.fn, 'validation').and.returnValue(true);
                loginAction.calls.reset();
                loginAction.and.returnValue(request.promise());
            });

            it('ignores repeated submissions while pending', function () {
                Component.login(form[0]);
                Component.login(form[0]);

                expect(loginAction.calls.count()).toBe(1);
            });

            it('allows retry after credential errors', function () {
                Component.login(form[0]);
                request.resolve({errors: true});
                Component.login(form[0]);

                expect(loginAction.calls.count()).toBe(2);
            });

            it('allows retry after a transport failure', function () {
                Component.login(form[0]);
                request.reject();
                Component.login(form[0]);

                expect(loginAction.calls.count()).toBe(2);
            });

            it('ignores submissions after success while navigation is pending', function () {
                Component.login(form[0]);
                request.resolve({errors: false});
                Component.login(form[0]);

                expect(loginAction.calls.count()).toBe(1);
            });
        });
    });
});
