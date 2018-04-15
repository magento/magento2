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

    /*
     * jQuery ui version 1.9.2 belongs to the adminhtml.
     *
     * This test will fail on frontend since mage/backend/form only belongs to backend.
     */
    if ($.ui.version === '1.9.2') {
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
                expect(handlers.join(' ')).toBe(form.data('form')._getHandlers().join(' '));
            });

            it('check store attribute', function () {
                var form = $(elementId).form(),
                    initialFormAttrs = {
                        action: form.attr('action'),
                        target: form.attr('target'),
                        method: form.attr('method')
                    };

                form.data('form')._storeAttribute('action');
                form.data('form')._storeAttribute('target');
                form.data('form')._storeAttribute('method');

                expect(form.data('form').oldAttributes.action).toBe(initialFormAttrs.action);
                expect(form.data('form').oldAttributes.target).toBe(initialFormAttrs.target);
                expect(form.data('form').oldAttributes.method).toBe(initialFormAttrs.method);
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

                form.data('form')._storeAttribute('action');
                expect(form.data('form')._getActionUrl(testArgs)).toBe(action + '/arg/value/');
                expect(form.data('form')._getActionUrl(testUrl)).toBe(testUrl);
                expect(form.data('form')._getActionUrl()).toBe(action);
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
                    processedData = form.data('form')._processData(testSimpleData);

                expect(form.data('form').oldAttributes.action).toBe(initialFormAttrs.action);
                expect(form.data('form').oldAttributes.target).toBe(initialFormAttrs.target);
                expect(form.data('form').oldAttributes.method).toBe(initialFormAttrs.method);
                expect(processedData.action).toBe(testSimpleData.action);
                expect(processedData.target).toBe(testSimpleData.target);
                expect(processedData.method).toBe(testSimpleData.method);

                form.data('form')._rollback();
                processedData = form.data('form')._processData(testActionArgsData);
                form.data('form')._storeAttribute('action');
                expect(processedData.action).toBe(form.data('form')._getActionUrl(testActionArgsData.action));
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

                form.data('form')._storeAttribute('action');
                resultData = form.data('form')._processData(resultData);
                testForm.prop(resultData);

                form.on('beforeSubmit', function (e, data) {
                    $.extend(data, beforeSubmitData);
                });

                form.on('submit', function (e) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                });

                form.data('form')._beforeSubmit('testHandler', eventData);
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

                form.data('form')._storeAttribute('action');
                form.data('form')._storeAttribute('target');
                form.data('form')._storeAttribute('method');

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

                form.data('form')._submit({
                    type: 'save'
                });

                expect(form.attr('action')).toBe(form.data('form').oldAttributes.action);
                expect(form.attr('target')).toBe(form.data('form').oldAttributes.target);
                expect(form.attr('method')).toBe(form.data('form').oldAttributes.method);
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
    }
});
