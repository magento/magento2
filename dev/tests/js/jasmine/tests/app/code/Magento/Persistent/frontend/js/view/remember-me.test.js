/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

define([
    'squire',
    'ko'
], function (Squire) {
    'use strict';

    /* eslint-disable max-nested-callbacks */
    describe('Magento_Persistent/js/view/remember-me', function () {
        let component,
            injector = new Squire();

        beforeEach(function (done) {
            window.checkoutConfig = {
                persistenceConfig: {
                    isRememberMeCheckboxVisible: true,
                    isRememberMeCheckboxChecked: false
                }
            };

            injector.require(['Magento_Persistent/js/view/remember-me'], function (RememberMe) {
                component = new RememberMe();
                done();
            });
        });

        it('should initialize with correct defaults', function () {
            expect(component.template).toBe('Magento_Persistent/remember-me');
            expect(component.isRememberMeCheckboxVisible()).toBe(true);
            expect(component.isRememberMeCheckboxChecked()).toBe(false);
        });

        it('should update isRememberMeCheckboxVisible observable', function () {
            component.isRememberMeCheckboxVisible(false);
            expect(component.isRememberMeCheckboxVisible()).toBe(false);
        });

        it('should update isRememberMeCheckboxChecked observable', function () {
            component.isRememberMeCheckboxChecked(true);
            expect(component.isRememberMeCheckboxChecked()).toBe(true);
        });
    });
});
