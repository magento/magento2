/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
/* jscs:disable jsDoc */

define([
    'jquery',
    'mage/backend/form'
], function ($) {
    'use strict';

    describe('Test for mage/form jQuery plugin', function () {
        var id = 'edit_form',
            elementId = '#' + id;

        beforeEach(function () {
            var element = $('<form id="' + id + '" action="action/url" method="GET" target="_self" ></form>');

            element.appendTo('body');
        });
        afterEach(function () {
            $(elementId).remove();
        });
        it('check if form can be initialized', function () {
            var form = $(elementId).form();

            expect(form.is(':mage-form')).toBeTruthy();
        });
        it('check get handlers', function () {
            var form = $(elementId).form(),
                handlersData = form.form('option', 'handlersData'),
                handlers = [];

            $.each(handlersData, function (key) {
                handlers.push(key);
            });
            expect(handlers.join(' ')).toBe(form.data('mageForm')._getHandlers().join(' '));
        });
        it('check store attribute', function () {
            var form = $(elementId).form(),
                initialFormAttrs = {
                    action: form.attr('action'),
                    target: form.attr('target'),
                    method: form.attr('method')
                };

            form.data('mageForm')._storeAttribute('action');
            form.data('mageForm')._storeAttribute('target');
            form.data('mageForm')._storeAttribute('method');
            expect(form.data('mageForm').oldAttributes.action).toBe(initialFormAttrs.action);
            expect(form.data('mageForm').oldAttributes.target).toBe(initialFormAttrs.target);
            expect(form.data('mageForm').oldAttributes.method).toBe(initialFormAttrs.method);
        });
        it('check bind', function () {
            var form = $(elementId).form(),
                submitted = false,
                handlersData = form.form('option', 'handlersData');

            form.on('submit', function (e) {
                submitted = true;
                e.stopImmediatePropagation();
                e.preventDefault();
            });
            $.each(handlersData, function (key) {
                form.trigger(key);
                expect(submitted).toBeTruthy();
                submitted = false;
            });
            form.off('submit');
        });
        it('check get action URL', function () {
            var form = $(elementId).form(),
                action = form.attr('action'),
                testUrl = 'new/action/url',
                testArgs = {
                    args: {
                        arg: 'value'
                    }
                };

            form.data('mageForm')._storeAttribute('action');
            expect(form.data('mageForm')._getActionUrl(testArgs)).toBe(action + '/arg/value/');
            expect(form.data('mageForm')._getActionUrl(testUrl)).toBe(testUrl);
            expect(form.data('mageForm')._getActionUrl()).toBe(action);
        });
        it('check process data', function () {
            var form = $(elementId).form(),
                initialFormAttrs = {
                    action: form.attr('action'),
                    target: form.attr('target'),
                    method: form.attr('method')
                },
                testSimpleData = {
                    action: 'new/action/url',
                    target: '_blank',
                    method: 'POST'
                },
                testActionArgsData = {
                    action: {
                        args: {
                            arg: 'value'
                        }
                    }
                },
                processedData = form.data('mageForm')._processData(testSimpleData);

            expect(form.data('mageForm').oldAttributes.action).toBe(initialFormAttrs.action);
            expect(form.data('mageForm').oldAttributes.target).toBe(initialFormAttrs.target);
            expect(form.data('mageForm').oldAttributes.method).toBe(initialFormAttrs.method);
            expect(processedData.action).toBe(testSimpleData.action);
            expect(processedData.target).toBe(testSimpleData.target);
            expect(processedData.method).toBe(testSimpleData.method);
            form.data('mageForm')._rollback();
            processedData = form.data('mageForm')._processData(testActionArgsData);
            form.data('mageForm')._storeAttribute('action');
            expect(processedData.action).toBe(form.data('mageForm')._getActionUrl(testActionArgsData.action));
        });
        it('check before submit', function () {
            var testForm = $('<form id="test-form"></form>').appendTo('body'),
                testHandler = {
                    action: {
                        args: {
                            arg1: 'value1'
                        }
                    }
                },
                form = $(elementId).form({
                    handlersData: {
                        testHandler: testHandler
                    }
                }),
                beforeSubmitData = {
                    action: {
                        args: {
                            arg2: 'value2'
                        }
                    },
                    target: '_blank'
                },
                eventData = {
                    method: 'POST'
                },
                resultData = $.extend(true, {}, testHandler, beforeSubmitData, eventData);

            form.data('mageForm')._storeAttribute('action');
            resultData = form.data('mageForm')._processData(resultData);
            testForm.prop(resultData);
            form.on('beforeSubmit', function (e, data) {
                $.extend(data, beforeSubmitData);
            });
            form.on('submit', function (e) {
                e.stopImmediatePropagation();
                e.preventDefault();
            });
            form.data('mageForm')._beforeSubmit('testHandler', eventData);
            expect(testForm.prop('action')).toBe(form.prop('action'));
            expect(testForm.prop('target')).toBe(form.prop('target'));
            expect(testForm.prop('method')).toBe(form.prop('method'));
        });
        it('check submit', function () {
            var formSubmitted = false,
                form = $(elementId).form({
                    handlersData: {
                        save: {}
                    }
                });

            form.data('mageForm')._storeAttribute('action');
            form.data('mageForm')._storeAttribute('target');
            form.data('mageForm')._storeAttribute('method');
            form.on('submit', function (e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                e.preventDefault();
                formSubmitted = true;
            }).prop({
                action: 'new/action/url',
                target: '_blank',
                method: 'POST'
            });
            form.data('mageForm')._submit({
                type: 'save'
            });
            expect(form.attr('action')).toBe(form.data('mageForm').oldAttributes.action);
            expect(form.attr('target')).toBe(form.data('mageForm').oldAttributes.target);
            expect(form.attr('method')).toBe(form.data('mageForm').oldAttributes.method);
            expect(formSubmitted).toBeTruthy();
            form.off('submit');
        });
        it('check build URL', function () {
            var dataProvider = [
                    {
                        params: ['http://domain.com//', {
                            'key[one]': 'value 1',
                            'key2': '# value'
                        }],
                        expected: 'http://domain.com/key[one]/value%201/key2/%23%20value/'
                    },
                    {
                        params: ['http://domain.com', {
                            'key[one]': 'value 1',
                            'key2': '# value'
                        }],
                        expected: 'http://domain.com/key[one]/value%201/key2/%23%20value/'
                    },
                    {
                        params: ['http://domain.com?some=param', {
                            'key[one]': 'value 1',
                            'key2': '# value'
                        }],
                        expected: 'http://domain.com?some=param&key[one]=value%201&key2=%23%20value'
                    },
                    {
                        params: ['http://domain.com?some=param&', {
                            'key[one]': 'value 1',
                            'key2': '# value'
                        }],
                        expected: 'http://domain.com?some=param&key[one]=value%201&key2=%23%20value'
                    }
                ],
                method = $.mage.form._proto._buildURL,
                quantity = dataProvider.length,
                i = 0;

            expect(quantity).toBeTruthy();

            for (i; i < quantity; i++) {
                expect(dataProvider[i].expected).toBe(method.apply(null, dataProvider[i].params));
            }
        });
    });
});
