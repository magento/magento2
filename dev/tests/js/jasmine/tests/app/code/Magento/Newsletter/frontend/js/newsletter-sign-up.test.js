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
        response = ko.observable({}),
        resolverMock = jasmine.createSpy('subscription-status-resolver', function (email, deferred) {
            if (response().errors) {
                deferred.reject();
            } else {
                deferred.resolve(response().subscribed);
            }
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

        it('Check sign-up process when Subscription is checked', function () {
            emailElem.val('email@example.com');
            checkbox.prop('checked', true);

            obj.updateSignUpStatus();

            expect(resolverMock).not.toHaveBeenCalled();
            expect(button.is(':disabled')).toBeFalsy();
            expect(checkbox.is(':checked')).toBeTruthy();
        });

        it('Check sign-up process without email', function () {
            checkbox.prop('checked', false);

            obj.updateSignUpStatus();

            expect(resolverMock).not.toHaveBeenCalled();
            expect(checkbox.is(':checked')).toBeFalsy();
        });

        it('Check sign-up process with incorrect email', function () {
            emailElem.val('emailexample.com');
            checkbox.prop('checked', false);

            obj.updateSignUpStatus();

            expect(resolverMock).not.toHaveBeenCalled();
            expect(checkbox.is(':checked')).toBeFalsy();
        });

        it('Check Subscription with correct data', function () {
            response({
                subscribed: true,
                errors: false
            });
            emailElem.val('email@example.com');
            checkbox.prop('checked', false);

            obj.updateSignUpStatus();

            expect(resolverMock).toHaveBeenCalled();
            expect(checkbox.is(':checked')).toBeTruthy();
            expect(button.is(':disabled')).toBeFalsy();
        });

        it('Check sign-up process with non-subscribed email', function () {
            response({
                subscribed: false,
                errors: false
            });
            emailElem.val('email@example.com');
            checkbox.prop('checked', false);

            obj.updateSignUpStatus();

            expect(resolverMock).toHaveBeenCalled();
            expect(checkbox.is(':checked')).toBeFalsy();
        });

        it('Check sign-up process with errors', function () {
            response({
                subscribed: true,
                errors: true
            });
            emailElem.val('email@example.com');
            checkbox.prop('checked', false);

            obj.updateSignUpStatus();

            expect(resolverMock).toHaveBeenCalled();
            expect(checkbox.is(':checked')).toBeFalsy();
        });
    });
});
