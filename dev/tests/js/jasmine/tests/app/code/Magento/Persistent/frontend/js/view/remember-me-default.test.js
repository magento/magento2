/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'ko',
    'squire'
], function (ko, Squire) {
    'use strict';

    describe('Magento_Persistent/js/view/remember-me-default', function () {
        let component,
            mocks = {
                'Magento_Customer/js/customer-data': {
                    get: jasmine.createSpy().and.returnValue(ko.observable({
                        isGuestCheckoutAllowed: false
                    }))
                }
            },
            injector = new Squire();

        beforeEach(function (done) {
            window.rememberMeConfig = {
                persistenceConfig: {
                    isRememberMeCheckboxVisible: true,
                    isRememberMeCheckboxChecked: true
                }
            };

            injector.mock(mocks);
            injector.require(['Magento_Persistent/js/view/remember-me-default'], function (RememberMe) {
                component = new RememberMe();
                done();
            });
        });

        it('should initialize with correct defaults', function () {
            expect(component.template).toBe('Magento_Persistent/remember-me');
            expect(component.isRememberMeCheckboxVisible()).toBe(true);
            expect(component.isRememberMeCheckboxChecked()).toBe(true);
        });

        it('should update isRememberMeCheckboxVisible observable', function () {
            component.isRememberMeCheckboxVisible(false);
            expect(component.isRememberMeCheckboxVisible()).toBe(false);
        });

        it('should update isRememberMeCheckboxChecked observable', function () {
            component.isRememberMeCheckboxChecked(false);
            expect(component.isRememberMeCheckboxChecked()).toBe(false);
        });

        it('should show remember me checkbox if guest checkout is allowed', function () {
            component.showElement();
            expect(component.isRememberMeCheckboxVisible()).toBe(true);
        });

        it('should hide remember me checkbox if guest checkout is not allowed', function () {
            let cart = mocks['Magento_Customer/js/customer-data'].get();

            spyOn(cart, 'subscribe').and.callFake(function (callback) {
                callback({ isGuestCheckoutAllowed: true });
            });
            component.showElement();
            expect(window.rememberMeConfig.persistenceConfig.isRememberMeCheckboxVisible).toBe(false);
        });
    });
});
