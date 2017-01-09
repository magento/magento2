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
    'Magento_Ui/js/form/components/html'
], function (_, registry, Constr) {
    'use strict';

    describe('Magento_Ui/js/form/components/html', function () {

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

        describe('"initObservable" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initObservable')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.initObservable;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.initObservable()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.initObservable();

                expect(type).toEqual('object');
            });
            it('Check called "this.observe" method', function () {
                obj.observe = jasmine.createSpy().and.callFake(function () {
                    return obj;
                });
                obj.initObservable();
                expect(obj.observe).toHaveBeenCalled();
            });
        });
        describe('"initContainer" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initContainer')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.initContainer;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called with arguments', function () {
                var parent = {
                    on: jasmine.createSpy()
                };

                expect(obj.initContainer(parent)).toBeDefined();
            });
            it('Check returned value type if method called with arguments', function () {
                var parent = {
                        on: jasmine.createSpy()
                    },
                    type = typeof obj.initContainer(parent);

                expect(type).toEqual('object');
            });
            it('Check called "arguments.on" method inner "initContainer" method', function () {
                var parent = {
                    on: jasmine.createSpy()
                };

                obj.initContainer(parent);
                expect(parent.on).toHaveBeenCalled();
            });
        });
        describe('"initAjaxConfig" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initAjaxConfig')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.initAjaxConfig;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.initAjaxConfig()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.initAjaxConfig();

                expect(type).toEqual('object');
            });
            it('Check url property', function () {
                obj.ajaxConfig = null;
                obj.url = 'magento';

                obj.initAjaxConfig();
                expect(obj.ajaxConfig.url).toEqual(obj.url);
            });
            it('Check FORM_KEY property', function () {
                obj.ajaxConfig = null;

                obj.initAjaxConfig();
                expect(obj.ajaxConfig.data.FORM_KEY).toEqual(window.FORM_KEY);
            });
            it('Check success property', function () {
                obj.ajaxConfig = null;

                obj.initAjaxConfig();
                expect(typeof obj.ajaxConfig.success).toEqual('function');
            });
        });
        describe('"onContainerToggle" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('onContainerToggle')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.onContainerToggle;

                expect(type).toEqual('function');
            });
            it('Check call method "this.shouldLoad" inner onContainerToggle method', function () {
                obj.shouldLoad = jasmine.createSpy().and.callFake(function () {
                    return true;
                });

                obj.onContainerToggle(true);
                expect(obj.shouldLoad).toHaveBeenCalled();
            });
            it('Check call method "this.loadData" inner onContainerToggle method', function () {
                obj.shouldLoad = jasmine.createSpy().and.callFake(function () {
                    return true;
                });
                obj.loadData = jasmine.createSpy().and.callFake(function () {
                    return true;
                });

                obj.onContainerToggle(true);
                expect(obj.loadData).toHaveBeenCalled();
            });
        });
        describe('"hasData" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('hasData')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.hasData;

                expect(type).toEqual('function');
            });
            it('Check returned type', function () {
                var type = typeof obj.hasData();

                expect(type).toEqual('boolean');
            });
        });
        describe('"shouldLoad" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('shouldLoad')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.shouldLoad;

                expect(type).toEqual('function');
            });
            it('Check returned type', function () {
                var type = typeof obj.shouldLoad();

                expect(type).toEqual('boolean');
            });
        });
        describe('"loadData" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('loadData')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.loadData;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.loadData()).toBeDefined();
            });
        });
        describe('"onDataLoaded" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('onDataLoaded')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.onDataLoaded;

                expect(type).toEqual('function');
            });
            it('Check call method "this.updateContent" inner onDataLoaded method', function () {
                var data = 'magento';

                obj.updateContent = jasmine.createSpy().and.callFake(function () {
                    return obj;
                });
                obj.onDataLoaded(data);
                expect(obj.loadData).toHaveBeenCalled();
            });
        });
        describe('"updateContent" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('updateContent')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.updateContent;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.updateContent()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                expect(typeof obj.updateContent()).toEqual('object');
            });
        });
    });
});
