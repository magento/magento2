/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'squire',
    'jquery',
    'prototype'
], function (Squire, $) {
    'use strict';

    describe('Magento_Ui/js/form/element/wysiwyg', function () {
        var injector, model, adapter, pluginButtons, initHandler, originalEditors, registry,
            wysiwygId = 'product_form_description';

        beforeEach(function (done) {
            var asyncQuery = function (element) {
                    return $(element);
                },
                mocks;

            injector = new Squire();
            asyncQuery.async = jasmine.createSpy('async');
            pluginButtons = {prop: jasmine.createSpy('prop')};
            adapter = {
                setEnabledStatus: jasmine.createSpy('setEnabledStatus'),
                activeEditor: jasmine.createSpy('activeEditor').and.returnValue(null),
                getPluginButtons: jasmine.createSpy('getPluginButtons').and.returnValue(pluginButtons)
            };
            originalEditors = window.tinyMceEditors;
            registry = $H({});
            registry.set(wysiwygId, adapter);
            spyOn(registry, 'get').and.callThrough();
            window.tinyMceEditors = registry;
            mocks = {
                'Magento_Ui/js/lib/registry/registry': {
                    /** Method stub. */
                    get: function () {
                        return {get: jasmine.createSpy(), set: jasmine.createSpy()};
                    },
                    options: jasmine.createSpy(),
                    create: jasmine.createSpy(),
                    set: jasmine.createSpy(),
                    async: jasmine.createSpy()
                },
                '/mage/utils/wrapper': jasmine.createSpy(),
                'Magento_Ui/js/lib/view/utils/async': asyncQuery,
                'wysiwygAdapter': {},
                'Magento_Variable/variables': {},
                'mage/adminhtml/events': {
                    attachEventHandler: function (event, handler) {
                        if (event === 'wysiwygEditorInitialized') {
                            initHandler = handler;
                        }
                    }
                }
            };
            injector.mock(mocks);
            injector.require([
                'Magento_Ui/js/form/element/wysiwyg',
                'knockoutjs/knockout-es5'
            ], function (Constr) {
                model = new Constr({
                    provider: 'provName',
                    name: 'description',
                    index: 'description',
                    dataScope: 'description',
                    content: '',
                    wysiwygId: wysiwygId,
                    disabled: true
                });
                done();
            });
        });

        afterEach(function () {
            window.tinyMceEditors = originalEditors;
            injector.clean();
            injector.remove();
        });

        it('resolves the adapter from the Hash and reapplies disabled state after editor initialization', function () {
            initHandler();

            expect(registry.get).toHaveBeenCalledWith(wysiwygId);
            expect(model.currentWysiwyg).toBe(adapter);
            expect(adapter.setEnabledStatus).toHaveBeenCalledWith(false);
            expect(pluginButtons.prop).toHaveBeenCalledWith('disabled', true);
        });

        it('disables the resolved adapter even when there is no active editor', function () {
            initHandler();
            adapter.setEnabledStatus.calls.reset();
            pluginButtons.prop.calls.reset();

            model.setDisabled(true);

            expect(adapter.setEnabledStatus).toHaveBeenCalledWith(false);
            expect(adapter.activeEditor).not.toHaveBeenCalled();
            expect(pluginButtons.prop).toHaveBeenCalledWith('disabled', true);
        });
    });
});
