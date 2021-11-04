/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'mage/translate-inline',
    'text!tests/assets/lib/web/mage/translate-inline.html'
], function ($, TranslateInline, translateTmpl) {
    'use strict';

    describe('mage/translate-inline', function () {
        describe('Check translate', function () {
            var translateSelector = '[data-role="translate-dialog"]',
                translateTemplateSelector = '#translate-form-template';

            beforeEach(function () {
                var translateBlock = $(translateTmpl);

                $('body').append(translateBlock);
            });

            afterEach(function () {
                $(translateSelector).remove();
                $(translateSelector).translateInline('destroy');
                $(translateTemplateSelector).remove();
            });

            it('Check that translate inited', function () {
                var translateInline = $(translateSelector).translateInline();

                expect(translateInline.is(':mage-translateInline')).toBe(true);
            });

            it('Check that translate hidden on init and visible on trigger', function () {
                var translateInline = $(translateSelector).translateInline({
                        id: 'dialog-id'
                    }),
                    isDialogHiddenOnInit = translateInline.is(':hidden'),
                    dialogVisibleAfterTriggerEdit;

                translateInline.trigger('edit.editTrigger');
                dialogVisibleAfterTriggerEdit = translateInline.is(':visible');
                expect(isDialogHiddenOnInit).toBe(true);
                expect(dialogVisibleAfterTriggerEdit).toBe(true);
            });

            it('Check translation form template', function () {
                var translateFormId = 'translate-form-id',
                    translateFormContent = 'New Template Variable',
                    translateInline = $(translateSelector).translateInline({
                        translateForm: {
                            data: {
                                id: translateFormId,
                                newTemplateVariable: translateFormContent
                            }
                        }
                    }),
                    $translateForm;

                translateInline.trigger('edit.editTrigger');
                $translateForm = $('#' + translateFormId);

                expect($translateForm.length).toBeGreaterThan(0);
                expect($translateForm.text()).toBe(translateFormContent);
            });

            it('Check translation submit', function () {
                var options = {
                        ajaxUrl: 'www.test.com',
                        area: 'test',
                        translateForm: {
                            template: '<form id="<%= data.id %>"><input name="test" value="test" /></form>',
                            data: {
                                id: 'translate-form-id'
                            }
                        }
                    },
                    expectedEequestData = 'area=test&test=test',
                    translateInline = $(translateSelector).translateInline(options),
                    $submitButton = $('.action-primary:contains(\'Submit\')'),
                    originalAjax = $.ajax;

                $.ajax = jasmine.createSpy().and.callFake(function (request) {
                    expect(request.url).toBe(options.ajaxUrl);
                    expect(request.type).toBe('POST');
                    expect(request.data).toBe(expectedEequestData);

                    return {
                        always: jasmine.createSpy()
                    };
                });

                translateInline.trigger('edit.editTrigger');
                $submitButton.trigger('click');
                $.ajax = originalAjax;
            });

            it('Check translation destroy', function () {
                var translateInline = $(translateSelector).translateInline();

                translateInline.trigger('edit.editTrigger');
                expect(translateInline.is(':mage-translateInline')).toBe(true);
                translateInline.translateInline('destroy');
                expect(translateInline.is(':mage-translateInline')).toBe(false);
            });
        });
    });
});
