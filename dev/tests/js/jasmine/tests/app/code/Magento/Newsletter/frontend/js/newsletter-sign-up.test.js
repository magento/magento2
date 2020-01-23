/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'squire',
    'ko',
    'jquery',
    'mage/validation'
], function (Squire, ko, $) {
    'use strict';

    var injector = new Squire(),
        obj,
        checkbox,
        emailElem,
        button,
        resolveStatus = ko.observable(true),
        resolverMock = jasmine.createSpy('subscription-status-resolver', function (email, deferred) {
            deferred.resolve(resolveStatus());
        }).and.callThrough(),
        mocks = {
            'Magento_Newsletter/js/subscription-status-resolver': resolverMock
        };

    beforeEach(function (done) {
        checkbox = $('<input type="checkbox" class="checkbox" name="is_subscribed" id="is_subscribed"/>');
        emailElem = $('<input type="email" name="email" id="email_address"/>');
        button = $('<button type="submit" id="button"/>');
        $(document.body).append(checkbox).append(emailElem).append(button);

        injector.mock(mocks);
        injector.require(['Magento_Newsletter/js/newsletter-sign-up'], function (Constr) {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: '',
                submitButton: '#button',
                signUpElement: '#is_subscribed'
            }, '#email_address');
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}

        checkbox.remove();
        emailElem.remove();
        button.remove();
    });

    describe('Magento_Newsletter/js/newsletter-sign-up', function () {
        it('Check for properties defined', function () {
            expect(obj.hasOwnProperty('submitButton')).toBeDefined();
            expect(typeof obj.submitButton).toEqual('string');
            expect(obj.hasOwnProperty('signUpElement')).toBeDefined();
            expect(typeof obj.signUpElement).toEqual('string');
            expect(obj.hasOwnProperty('element')).toBeDefined();
            expect(typeof obj.element).toEqual('string');
        });

        it('Verify Subscription is checked', function () {
            emailElem.val('email@example.com');
            checkbox.prop('checked', true);
            expect(checkbox.is(':checked')).toBeTruthy();

            obj.updateSignUpStatus();

            expect(resolverMock).not.toHaveBeenCalled();
            expect(button.is(':disabled')).toBeFalsy();
            expect(checkbox.is(':checked')).toBeTruthy();
        });

        it('Verify sign-up process without email', function () {
            checkbox.prop('checked', false);
            expect(checkbox.is(':checked')).toBeFalsy();

            obj.updateSignUpStatus();

            expect(resolverMock).not.toHaveBeenCalled();
            expect(checkbox.is(':checked')).toBeFalsy();
        });

        it('Verify sign-up process with incorrect email', function () {
            emailElem.val('emailexample.com');
            checkbox.prop('checked', false);
            expect(checkbox.is(':checked')).toBeFalsy();

            obj.updateSignUpStatus();

            expect(resolverMock).not.toHaveBeenCalled();
            expect(checkbox.is(':checked')).toBeFalsy();
        });

        it('Verify Subscription with correct data', function () {
            emailElem.val('email@example.com');
            checkbox.prop('checked', false);
            expect(checkbox.is(':checked')).toBeFalsy();

            obj.updateSignUpStatus();

            expect(resolverMock).toHaveBeenCalled();
            expect(checkbox.is(':checked')).toBeTruthy();
            expect(button.is(':disabled')).toBeFalsy();
        });

        it('Verify sign-up process with non-subscribed email', function () {
            resolveStatus(false);
            emailElem.val('email@example.com');
            checkbox.prop('checked', false);
            expect(checkbox.is(':checked')).toBeFalsy();

            obj.updateSignUpStatus();

            expect(resolverMock).toHaveBeenCalled();
            expect(checkbox.is(':checked')).toBeFalsy();
        });
    });
});
