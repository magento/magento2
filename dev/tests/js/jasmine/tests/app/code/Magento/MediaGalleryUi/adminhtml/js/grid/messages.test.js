/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
            var escapedMessage = 'escaped message';

            // eslint-disable-next-line max-nested-callbacks
            spyOn(escaperInstance, 'escapeHtml').and.callFake(function () {
                return escapedMessage;
            });

            expect(messagesInstance.prepareMessageUnsanitizedHtml(messageText)).toEqual(escapedMessage);
        });
    });
});
