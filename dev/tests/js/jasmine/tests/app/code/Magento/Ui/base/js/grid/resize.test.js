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
    'ko',
    'Magento_Ui/js/grid/resize',
    'Magento_Ui/js/lib/view/utils/dom-observer',
    'Magento_Ui/js/lib/view/utils/async'
], function (_, registry, ko, Constr, observer,$) {
    'use strict';

    describe('Magento_Ui/js/grid/resize', function () {
        var obj = new Constr({
                dataScope: '',
                columnsProvider: 'magento',
                provider: 'provider',
                name: 'magento',
                index: 'magento'
            }),
            type,
            arg,
            event;

        describe('"initialize" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initialize')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.initialize;
                expect(type).toEqual('function');
            });
        });
        describe('"initTable" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initTable')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.initTable;
                expect(type).toEqual('function');
            });
            it('Check returned value type if method called without arguments', function () {
                type = typeof obj.initTable();
                expect(type).toEqual('object');
            });
            it('Check "this.table" variable', function () {
                arg = document.createElement('table');
                obj.initTable(arg);
                expect(arg.classList.contains(obj.fixedLayoutClass)).toBeTruthy();
            });
        });
        describe('"initColumn" method', function () {
            beforeEach(function(){
                spyOn(ko, 'dataFor').and.callFake(function (data) {
                    return {
                        index: 1,
                        column: data,
                        on: function (arg1, arg2) {}
                    }
                });
                spyOn(ko, 'contextFor').and.callFake(function () {
                    return {$index: 1, $parent: obj}
                });
                $._data = jasmine.createSpy().and.callFake(function () {
                    return {
                        click: [{}, {}],
                        mousedown: [{}, {}]
                    };
                });
            });
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initColumn')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.initColumn;
                expect(type).toEqual('function');
            });
            it('Check call "this.getDefaultWidth" method', function () {
                spyOn(obj, 'getDefaultWidth');
                obj.initColumn('magento');
                expect(obj.getDefaultWidth).toHaveBeenCalledWith('magento');
            });
            it('Check call "this.hasColumn" method', function () {
                spyOn(obj, 'hasColumn').and.callFake(function () {
                    return false;
                });
                obj.initColumn('magento');
                expect(obj.hasColumn).toHaveBeenCalled();
            });
            it('Check call "this.initResizableElement" method', function () {
                spyOn(obj, 'hasColumn').and.callFake(function () {
                    return false;
                });
                spyOn(obj, 'initResizableElement').and.callFake(function (arg) {
                    return true;
                });
                obj.initColumn('magento');
                expect(obj.initResizableElement).toHaveBeenCalled();
            });
            it('Check call "this.setStopPropagationHandler" method', function () {
                spyOn(obj, 'hasColumn').and.callFake(function () {
                    return false;
                });
                spyOn(obj, 'setStopPropagationHandler').and.callFake(function (arg) {
                    return true;
                });
                obj.initColumn('magento');
                expect(obj.setStopPropagationHandler).toHaveBeenCalledWith('magento');
            });
            it('Check call "this.refreshLastColumn" method', function () {
                spyOn(obj, 'refreshLastColumn').and.callFake(function (arg) {
                    return true;
                });
                obj.initColumn('magento');
                expect(obj.refreshLastColumn).toHaveBeenCalledWith('magento');
            });
        });
        describe('"initResizableElement" method', function () {
            beforeEach(function(){
                spyOn(ko, 'dataFor').and.callFake(function (data) {
                    return {
                        index: 1,
                        column: data,
                        on: function (arg1, arg2) {}
                    }
                });
                spyOn(ko, 'contextFor').and.callFake(function () {
                    return {$index: 1, $parent: obj}
                });
            });
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initResizableElement')).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                type = typeof obj.initResizableElement('magento');
                expect(type).toEqual('boolean');
            });
            it('Check returned value', function () {
                expect(obj.initResizableElement()).toEqual(true);
            });
        });
        describe('"setStopPropagationHandler" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('setStopPropagationHandler')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.setStopPropagationHandler;
                expect(type).toEqual('function');
            });
            it('Check returned value type if method called without arguments', function () {
                type = typeof obj.setStopPropagationHandler('magento');
                expect(type).toEqual('object');
            });
        });
        describe('"refreshLastColumn" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('refreshLastColumn')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.setStopPropagationHandler;
                expect(type).toEqual('function');
            });
        });
        describe('"refreshMaxRowHeight" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('refreshMaxRowHeight')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.refreshMaxRowHeight;
                expect(type).toEqual('function');
            });
            it('Check call "this.hasRow" method', function () {
                spyOn(obj, 'hasRow').and.callFake(function () {
                    return true;
                });
                obj.refreshMaxRowHeight('magento');
                expect(obj.hasRow).toHaveBeenCalled();
            });
        });
        describe('"mousedownHandler" method', function () {
            beforeEach(function(){
                spyOn(ko, 'dataFor').and.callFake(function (data) {
                    return {
                        index: 1,
                        column: data,
                        on: function (arg1, arg2) {}
                    }
                });
                spyOn(ko, 'contextFor').and.callFake(function () {
                    return {$index: ko.observable(1), $parent: obj}
                });
                spyOn(obj, 'getNextElement').and.callFake(function () {
                    return true;
                });
                event = {stopImmediatePropagation: function(){}}
            });
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('mousedownHandler')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.mousedownHandler;
                expect(type).toEqual('function');
            });
            it('Check call "this.hasColumn" method', function () {
                spyOn(obj, 'hasColumn').and.callFake(function () {
                    return true;
                });
                obj.mousedownHandler(event);
                expect(obj.hasColumn).toHaveBeenCalled();
            });
            it('Check call "this.getNextElement" method', function () {
                obj.mousedownHandler(event);
                expect(obj.getNextElement).toHaveBeenCalled();
            });
        });
        describe('"mousemoveHandler" method', function () {
            beforeEach(function(){
                event = {stopImmediatePropagation: function(){}}
            });
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('mousemoveHandler')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.mousemoveHandler;
                expect(type).toEqual('function');
            });
        });
        describe('"mouseupHandler" method', function () {

            beforeEach(function(){
                event = {stopPropagation: function () {}, preventDefault: function () {}}
            });
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('mouseupHandler')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.mousemoveHandler;
                expect(type).toEqual('function');
            });
            it('Check call "this.store" method', function () {
                spyOn(obj, 'store').and.callFake(function () {
                    return true;
                });
                obj.mouseupHandler(event);
                expect(obj.store).toHaveBeenCalled();
            });
            it('Check "this.storageColumnsData" property change', function () {
                obj.resizeConfig.curResizeElem.model.index = 1;
                obj.resizeConfig.depResizeElem.model.index = 2;
                obj.resizeConfig.curResizeElem.model.width = 100;
                obj.resizeConfig.depResizeElem.model.width = 200;
                obj.mouseupHandler(event);
                expect(obj.storageColumnsData[1]).toEqual(100);
                expect(obj.storageColumnsData[2]).toEqual(200);
            });
        });
        describe('"getNextElement" method', function () {
            beforeEach(function(){
                spyOn(ko, 'dataFor').and.callFake(function (data) {
                    return {
                        index: 1,
                        column: data,
                        visible: function () {
                            return true;
                        },
                        on: function (arg1, arg2) {}
                    }
                });
            });
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('getNextElement')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.getNextElement;
                expect(type).toEqual('function');
            });
            it('Check call "this.hasColumn" method', function () {
                spyOn(obj, 'hasColumn').and.callFake(function () {
                    return 'magento';
                });
                obj.getNextElement('magento');
                expect(obj.hasColumn).toHaveBeenCalled();
            });
            it('Check returned value', function () {
                spyOn(obj, 'hasColumn').and.callFake(function () {
                    return 'magento';
                });
                expect(obj.getNextElement('magento')).toEqual('magento');
            });
        });
        describe('"getDefaultWidth" method', function () {
            beforeEach(function(){
                spyOn(ko, 'dataFor').and.callFake(function (data) {
                    return {
                        index: 1,
                        column: data,
                        resizeDefaultWidth: 200,
                        visible: function () {
                            return true;
                        },
                        on: function (arg1, arg2) {}
                    }
                });
            });
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('getDefaultWidth')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.getDefaultWidth;
                expect(type).toEqual('function');
            });
            it('Check return value if storage has data', function () {
                obj.storageColumnsData[1] = 100;
                expect(obj.getDefaultWidth('magento')).toEqual(100);
            });
            it('Check return value if storage has not data but width sets in config', function () {
                obj.storageColumnsData[1] = 0;
                expect(obj.getDefaultWidth('magento')).toEqual(200);
            });
        });
        describe('"hasColumn" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('hasColumn')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.hasColumn;
                expect(type).toEqual('function');
            });
            it('Check return value type if "returned" arguments if false', function () {
                arg = { index: 'magento' };
                expect(typeof obj.hasColumn(arg, false)).toEqual('boolean');
            });
            it('Must return false if object columnsElements has not model.index property', function () {
                arg = { index: 'magento' };
                obj.columnsElements = {};
                expect(obj.hasColumn(arg, false)).toEqual(false);
            });
            it('Must return true if object columnsElements has  model.index property', function () {
                arg = { index: 'magento' };
                obj.columnsElements = {magento: 'magentoProp'};
                expect(obj.hasColumn(arg, false)).toEqual(true);
            });
            it('Must return property if object columnsElements has property and second argument is true', function () {
                arg = { index: 'magento' };
                obj.columnsElements = {magento: 'magentoProp'};
                expect(obj.hasColumn(arg, true)).toEqual('magentoProp');
            });
        });
        describe('"hasRow" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('hasRow')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.hasRow;
                expect(type).toEqual('function');
            });
            it('Check return value type if "returned" arguments if false', function () {
                arg = { elem: 'magento' };
                expect(typeof obj.hasRow(arg, false)).toEqual('boolean');
            });
            it('Must return false if object maxRowsHeight has not elem property', function () {
                arg = { elem: 'magento' };
                obj.maxRowsHeight([]);
                expect(obj.hasRow(arg, false)).toEqual(false);
            });
            it('Must return true if object maxRowsHeight has  elem property', function () {
                arg = 'magento';
                obj.maxRowsHeight([{elem: 'magento'}]);
                expect(obj.hasRow(arg, false)).toEqual(true);
            });
            it('Must return property if object maxRowsHeight has property and second argument is true', function () {
                arg = 'magento';
                obj.maxRowsHeight([{elem: 'magento'}]);
                expect(typeof obj.hasRow(arg, true)).toEqual('object');
            });
        });
    });
});
