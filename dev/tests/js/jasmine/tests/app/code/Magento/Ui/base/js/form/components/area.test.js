/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
/*jscs:disable requirePaddingNewLinesInObjects*/
/*jscs:disable jsDoc*/

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/components/area'
], function (_, registry, Constr) {
    'use strict';

    describe('Magento_Ui/js/form/components/area', function () {
        var obj = new Constr({
            provider: 'provName',
            name: '',
            index: ''
        });

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
                obj.observe = jasmine.createSpy();
                obj.initObservable();
                expect(obj.observe).toHaveBeenCalled();
            });
        });
        describe('"initElement" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initElement')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.initElement;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called with object argument', function () {
                var arg = {
                    initContainer: function () {
                    },
                    on: function () {
                    }
                };

                expect(obj.initElement(arg)).toBeDefined();
            });
            it('Check returned value type if method called with object argument', function () {
                var arg = {
                        initContainer: function () {
                        },
                        on: function () {
                        }
                    },
                    type = typeof obj.initElement(arg);

                expect(type).toEqual('object');
            });
        });
        describe('"onChildrenUpdate" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('onChildrenUpdate')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.onChildrenUpdate;

                expect(type).toEqual('function');
            });
            it('Check called "this.delegate" method ', function () {
                obj.delegate = jasmine.createSpy();
                obj.onChildrenUpdate();
                expect(obj.delegate).toHaveBeenCalled();
            });
        });
        describe('"onContentLoading" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('onContentLoading')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.onContentLoading;

                expect(type).toEqual('function');
            });
            it('Try called', function () {
                obj.onContentLoading = jasmine.createSpy();
                obj.onContentLoading();
                expect(obj.onContentLoading).toHaveBeenCalled();
            });
        });
    });
});
