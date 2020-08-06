/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_MediaGalleryUi/js/grid/messages'
], function (Messages) {
    'use strict';

    describe('Magento_MediaGalleryUi/js/grid/messages', function () {
        var message,
            messageText,
            errorType,
            successType;

        beforeEach(function () {
            message = Messages;
            messageText = 'test message';
            errorType = 'error';
            successType = 'success';
        });

        describe('message handling', function () {
            it('add error message, get error message', function () {
                message.add(errorType, messageText);
                expect(message.get()).toEqual([messageText]);
            });

            it('add success message, get success message', function () {
                message.add(successType, messageText);
                expect(message.get()).toEqual([messageText]);
            });

            it('scheduled cleaning messages', function () {
                message.add(errorType, messageText);
                message.scheduleCleanup();
                expect(message.get()).toEqual([]);
            });
        });

        describe('prepareMessageUnsanitizedHtml', function () {
            var messageData,
                expectedData;

            beforeEach(function () {
                messageData = 'Login failed. Please check if the <a href="%1">Secret Key</a> is set correctly and try again.';
                expectedData = 'Login failed. Please check if the <a href="%1">Secret Key</a> is set correctly and try again.';
            });

            it('prepare message to be rendered as HTML', function () {
                expect(message.prepareMessageUnsanitizedHtml(messageData)).toEqual(expectedData)
            });
        });
    });
});
