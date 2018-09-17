/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
/*jscs:disable requirePaddingNewLinesInObjects*/
/*jscs:disable jsDoc*/

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/components/group'
], function (_, registry, Constr) {
    'use strict';

    describe('Magento_Ui/js/form/components/group', function () {

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
                obj.observe = jasmine.createSpy().and.callFake(function () {
                    return obj;
                });
                obj.initObservable();
                expect(obj.observe).toHaveBeenCalled();
            });
        });
        describe('"isSingle" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('isSingle')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.isSingle;

                expect(type).toEqual('function');
            });
            it('Check returned value if this.elems.getLength return 1', function () {
                obj.elems.getLength = jasmine.createSpy().and.callFake(function () {
                    return 1;
                });

                expect(obj.isSingle()).toEqual(true);
            });
            it('Check returned value if this.elems.getLength return not 1', function () {
                obj.elems.getLength = jasmine.createSpy().and.callFake(function () {
                    return 2;
                });

                expect(obj.isSingle()).toEqual(false);
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.isSingle();

                expect(type).toEqual('boolean');
            });
            it('Check called "this.observe" method', function () {
                obj.elems.getLength = jasmine.createSpy().and.callFake(function () {
                    return 1;
                });
                obj.isSingle();
                expect(obj.elems.getLength).toHaveBeenCalled();
            });
        });
        describe('"isMultiple" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('isMultiple')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.isMultiple;

                expect(type).toEqual('function');
            });
            it('Check returned value if this.elems.getLength return more 1', function () {
                obj.elems.getLength = jasmine.createSpy().and.callFake(function () {
                    return 4;
                });

                expect(obj.isMultiple()).toEqual(true);
            });
            it('Check returned value if this.elems.getLength return 1 or less', function () {
                obj.elems.getLength = jasmine.createSpy().and.callFake(function () {
                    return 1;
                });

                expect(obj.isMultiple()).toEqual(false);
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.isMultiple();

                expect(type).toEqual('boolean');
            });
            it('Check called "this.observe" method', function () {
                obj.elems.getLength = jasmine.createSpy().and.callFake(function () {
                    return 1;
                });
                obj.isMultiple();
                expect(obj.elems.getLength).toHaveBeenCalled();
            });
        });
    });
});
