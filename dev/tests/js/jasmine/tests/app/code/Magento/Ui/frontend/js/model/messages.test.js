/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiRegistry',
    'Magento_Ui/js/model/messages'
], function (registry, Constr) {
    'use strict';

    describe('Magento_Ui/js/model/messages', function () {

        var obj,
            errorMessageText,
            successMessageText,
            messageObj;

        beforeEach(function () {
            obj = new Constr(
                {
                    provider: 'provName',
                    name: '',
                    index: ''
                });
            errorMessageText = 'Error message test';
            successMessageText = 'Success message test';

            registry.set('provName', {
                /** Stub */
                on: function () {
                },

                /** Stub */
                get: function () {
                },

                /** Stub */
                set: function () {
                }
            });
        });

        it('simple message', function () {
            var messageObj =
                {
                    message: 'Message test'
                },
                type = [];

            expect(obj.add(messageObj, type)).toEqual(true);
            expect(type).toEqual([messageObj.message]);
        });

        it('message with parameters', function () {
            var returnedObj,
                type = [];

            messageObj = {
                message: 'Message test case %1, case %2 and case %3',
                parameters: [
                    'one',
                    'two',
                    'three'
                ]
            };
            returnedObj = ['Message test case ' + messageObj.parameters[0] + ', case ' +
                messageObj.parameters[1] + ' and case ' + messageObj.parameters[2]];

            expect(obj.add(messageObj, type)).toEqual(true);
            expect(type).toEqual(returnedObj);
        });

        it('check addErrorMessage && getErrorMessage && hasMessages', function () {
            messageObj = {
                message: errorMessageText
            };

            expect(obj.hasMessages()).toEqual(false);
            expect(obj.addErrorMessage(messageObj)).toEqual(true);
            expect(obj.getErrorMessages()()).toEqual([errorMessageText]);
            expect(obj.hasMessages()).toEqual(true);
        });

        it('check addSuccessMessage && getSuccessMessage && hasMessages', function () {
            messageObj = {
                message: successMessageText
            };

            expect(obj.addSuccessMessage(messageObj)).toEqual(true);
            expect(obj.getSuccessMessages()()).toEqual([successMessageText]);
            expect(obj.hasMessages()).toEqual(true);
        });

        it('check for cleaning messages', function () {
            messageObj = {
                message: 'Message test case %1, case %2 and case %3',
                parameters: [
                    'one',
                    'two',
                    'three'
                ]
            };
            expect(obj.addErrorMessage(messageObj)).toEqual(true);
            obj.clear();
            expect(obj.getErrorMessages()()).toEqual([]);
            expect(obj.hasMessages()).toEqual(false);
        });
    });
});
