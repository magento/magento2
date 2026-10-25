/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

define([
    'Magento_MediaGalleryUi/js/grid/messages',
    'escaper'
], function (Messages, Escaper) {
    'use strict';

    describe('Magento_MediaGalleryUi/js/grid/messages', function () {
        var messagesInstance,
            escaperInstance,
            messageText,
            errorType,
            successType;

        beforeEach(function () {
            escaperInstance = Escaper;
            messagesInstance = Messages({
                escaper: escaperInstance
            });
            messageText = 'test message';
            errorType = 'error';
            successType = 'success';
        });

        it('add error message, get error message', function () {
            messagesInstance.add(errorType, messageText);
            expect(JSON.stringify(messagesInstance.get())).toEqual(JSON.stringify([{
                code: errorType,
                message: messageText
            }]));
        });

        it('add success message, get success message', function () {
            messagesInstance.add(successType, messageText);
            expect(JSON.stringify(messagesInstance.get())).toEqual(JSON.stringify([{
                code: successType,
                message: messageText
            }]));
        });

        it('handles multiple messages', function () {
            messagesInstance.add(successType, messageText);
            messagesInstance.add(errorType, messageText);
            expect(JSON.stringify(messagesInstance.get())).toEqual(JSON.stringify([
                {
                    code: successType,
                    message: messageText
                },
                {
                    code: errorType,
                    message: messageText
                }
            ]));
        });

        it('cleans messages', function () {
            messagesInstance.add(errorType, messageText);
            messagesInstance.clear();

            expect(JSON.stringify(messagesInstance.get())).toEqual(JSON.stringify([]));
        });

        it('prepare message to be rendered as HTML', function () {
            var escapedMessage = 'escaped message',
                originalEscapeHtml = escaperInstance.escapeHtml;

            // eslint-disable-next-line max-nested-callbacks
            escaperInstance.escapeHtml = jasmine.createSpy().and.callFake(function () {
                return escapedMessage;
            });

            expect(messagesInstance.prepareMessageUnsanitizedHtml(messageText)).toEqual(escapedMessage);

            // Restore original function to avoid test interference
            escaperInstance.escapeHtml = originalEscapeHtml;
        });
    });
});
