/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
/*jscs:disable requirePaddingNewLinesInObjects*/
/*jscs:disable jsDoc*/

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/client',
    'jquery',
    'mageUtils',
    'jquery/ui'
], function (_, registry, Constr, $, utils) {
    'use strict';

    describe('Magento_Ui/js/form/client', function () {
        var obj = new Constr({
            provider: 'provName',
            name: '',
            index: ''
        });

        window.FORM_KEY = 'magentoFormKey';

        registry.set('provName', {
            on: function () {
            },
            get: function () {
            },
            set: function () {
            }
        });

        describe('"save" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('save')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.save;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                obj.urls = {};
                obj.urls.beforeSave = {};
                expect(obj.save()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.save();

                expect(type).toEqual('object');
            });
            it('Check "beforeSave" method. ' +
                'Check calls "filterFormData", "serialize" and "ajax" inside themselves.', function () {
                var data = {
                        key: {
                            anotherKey: 'value'
                        },
                        anotherKey: []
                    },
                    params;

                obj.urls.beforeSave = 'requestPath';
                obj.selectorPrefix = 'selectorPrefix';
                obj.messagesClass = 'messagesClass';

                params = {
                    url: obj.urls.beforeSave,
                    data: _.extend(data, {
                        form_key: 'magentoFormKey'
                    }),
                    success: jasmine.any(Function),
                    complete: jasmine.any(Function)
                };

                utils.filterFormData = jasmine.createSpy().and.returnValue(data);
                utils.serialize = jasmine.createSpy().and.returnValue(data);

                $.ajax = jasmine.createSpy();
                obj.save(data);
                expect(utils.filterFormData).toHaveBeenCalledWith(data);
                expect(utils.serialize).toHaveBeenCalledWith(data);
                expect($.ajax).toHaveBeenCalledWith(params);

            });
            it('Check call "beforeSave" method without parameters', function () {
                $.ajax = jasmine.createSpy();
                obj.urls.beforeSave = null;
                obj.save();

                expect($.ajax).not.toHaveBeenCalled();
            });
            it('Check call "beforeSave" method. Check "success" ajax callback with success response.' , function () {
                var request;

                $.ajax = jasmine.createSpy().and.callFake(function (req) {
                    request = req.success;
                });
                $.fn.notification = jasmine.createSpy();
                obj.urls.beforeSave = 'requestPath';
                obj.save();

                expect(request({error: false})).toBe(true);
                expect($('body').notification).not.toHaveBeenCalledWith('clear');
            });

            it('Check call "beforeSave" method. Check "success" ajax callback with error response.' , function () {
                var request,
                    notificationArguments;

                $.ajax = jasmine.createSpy().and.callFake(function (req) {
                    request = req.success;
                });
                $.fn.notification = jasmine.createSpy();
                obj.urls.beforeSave = 'requestPath';
                obj.save();

                notificationArguments = {
                    error: true,
                    message: 1,
                    insertMethod: jasmine.any(Function)
                };

                expect(request({
                    error: true,
                    messages: [1]
                })).toBeUndefined();
                expect($('body').notification.calls.allArgs()).toEqual([['clear'], ['add', notificationArguments]]);
            });
            it('Check call "beforeSave" method. Check "complete" ajax callback.' , function () {
                var request;

                $.ajax = jasmine.createSpy().and.callFake(function (req) {
                    request = req.complete;
                });
                $.fn.trigger = jasmine.createSpy();
                obj.urls.beforeSave = 'requestPath';
                obj.save();

                expect(request()).toBeUndefined();
                expect($('body').trigger).toHaveBeenCalledWith('processStop');
            });
        });

        describe('"initialize" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initialize')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.initialize;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.initialize()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.initialize();

                expect(type).toEqual('object');
            });
        });
    });
});
