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
    'mageUtils'
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
            it('Check "beforeSave" method. Check call "filterFormData" inside themselves.', function () {
                var data = {
                        key: {
                            anotherKey: 'value'
                        },
                        anotherKey: []
                    };

                obj.urls.beforeSave = 'requestPath';
                obj.selectorPrefix = 'selectorPrefix';
                obj.messagesClass = 'messagesClass';
                utils.filterFormData = jasmine.createSpy().and.returnValue(utils.filterFormData(data));

                obj.save(data);
                expect(utils.filterFormData).toHaveBeenCalledWith(data);
            });
            it('Check "beforeSave" method. Check call "serialize" inside themselves.', function () {
                var data = {
                        key: {
                            anotherKey: 'value'
                        },
                        anotherKey: []
                    };

                obj.urls.beforeSave = 'requestPath';
                obj.selectorPrefix = 'selectorPrefix';
                obj.messagesClass = 'messagesClass';
                utils.serialize = jasmine.createSpy().and.returnValue(utils.serialize(data));

                obj.save(data);
                expect(utils.serialize).toHaveBeenCalledWith(data);
            });
            it('Check "beforeSave" method. Check call "ajax" inside themselves.', function () {
                var data = {
                        key: {
                            anotherKey: 'value'
                        },
                        'anotherKey-prepared-for-send': []
                    },
                    result = {
                        url: obj.urls.beforeSave,
                        data: {
                            'key[anotherKey]': 'value',
                            'form_key': 'magentoFormKey'
                        },
                        success: jasmine.any(Function),
                        complete: jasmine.any(Function)
                    };

                obj.urls.beforeSave = 'requestPath';
                obj.selectorPrefix = 'selectorPrefix';
                obj.messagesClass = 'messagesClass';
                $.ajax = jasmine.createSpy();

                obj.save(data);
                expect($.ajax).toHaveBeenCalledWith(result);
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
