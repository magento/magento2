/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'uiRegistry',
    'Magento_Ui/js/model/messages'
], function (ko, registry, Constr) {
    'use strict';

    describe('Magento_Ui/js/model/messages', function () {
        var obj = new Constr({
            provider: 'provName',
            name: '',
            index: ''
        });

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

        describe('initialize method', function () {
            it('check for existing', function () {
                expect(obj).toBeDefined();
            });
        });

        describe('add method', function () {
            it('simple message', function () {
                var messageObj = {
                        message: "Message test"
                    },
                    type = [],
                    returnedObj = ["Message test"];
                expect(obj.add(messageObj, type)).toEqual(true);
                expect(type).toEqual(returnedObj);
            });

            it('message with parameters', function () {
                var messageObj = {
                        message: "Message test case %1, case %2 and case %3",
                        parameters: [
                            "one",
                            "two",
                            'three'
                        ]
                    },
                    type = [],
                    returnedObj = ["Message test case " + messageObj.parameters[0] + ", case " +
                    messageObj.parameters[1] + " and case " + messageObj.parameters[2]];
                expect(obj.add(messageObj, type)).toEqual(true);
                expect(type).toEqual(returnedObj);
            });
        });

        describe('check methods: hasMessages, addErrorMessage, getErrorMessages', function () {
            it('hasMessages method before adding messages', function () {
                expect(obj.hasMessages()).toEqual(false);
            });

            it('check addErrorMessage method', function () {
                var messageObj = {
                    message: "Error message test"
                };

                expect(obj.addErrorMessage(messageObj)).toEqual(true);
            });

            it('check getErrorMessage method', function () {
                var errorMessages = ko.observableArray(["Error message test"]);

                expect(obj.getErrorMessages()()).toEqual(errorMessages());
            });

            it('hasMessages method after adding Error messages', function () {
                expect(obj.hasMessages()).toEqual(true);
            });
        });

        describe('check clean method for Error messages', function () {
            it('check for cleaning messages', function () {
                obj.clear();
                expect(obj.getErrorMessages()()).toEqual([]);
                expect(obj.hasMessages()).toEqual(false);
            });
        });

        describe('check methods: hasMessages, addSuccessMessage, getSuccessMessages', function () {
            it('check addSuccessMessage and getSuccessMessage', function () {
                var messageObj = {
                    message: "Success message test"
                };

                expect(obj.addSuccessMessage(messageObj)).toEqual(true);
            });

            it('check method getSuccessMessage', function () {
                var successMessages = ko.observableArray(["Success message test"]);
                expect(obj.getSuccessMessages()()).toEqual(successMessages());
            });

            it('hasMessages method after adding Success messages', function () {
                expect(obj.hasMessages()).toEqual(true);
            });
        });

        describe('check clean method for Success messages', function () {
            it('check for cleaning messages', function () {
                obj.clear();
                expect(obj.getSuccessMessages()()).toEqual([]);
                expect(obj.hasMessages()).toEqual(false);
            });
        });
    });
});
