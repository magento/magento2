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
    'Magento_Ui/js/form/form'
], function (_, registry, Constr) {
    'use strict';

    describe('Magento_Ui/js/form/form', function () {

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

        describe('"initAdapter" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initAdapter')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.save;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.initAdapter()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.initAdapter();

                expect(type).toEqual('object');
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
        describe('"initProperties" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initProperties')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.initProperties;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.initProperties()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.initProperties();

                expect(type).toEqual('object');
            });
            it('Check this.selector property (is modify in initProperties method)', function () {
                obj.selector = null;
                obj.initProperties();
                expect(typeof obj.selector).toEqual('string');
            });
        });
        describe('"hideLoader" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('hideLoader')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.hideLoader;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.hideLoader()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.hideLoader();

                expect(type).toEqual('object');
            });
        });
        describe('"save" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('save')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.save;

                expect(type).toEqual('function');
            });
            it('Check call method "this.validate" inner save method', function () {
                obj.validate = jasmine.createSpy();
                obj.source.get = jasmine.createSpy().and.callFake(function () {
                    return true;
                });
                obj.save();
                expect(obj.validate).toHaveBeenCalled();
            });
            it('Check call method "this.source.get" inner save method', function () {
                obj.validate = jasmine.createSpy();
                obj.source.get = jasmine.createSpy().and.callFake(function () {
                    return true;
                });
                obj.save();
                expect(obj.source.get).toHaveBeenCalled();
            });
            it('Check call method "this.submit" inner save method', function () {
                obj.validate = jasmine.createSpy();
                obj.source.get = jasmine.createSpy().and.callFake(function () {
                    return false;
                });
                obj.submit = jasmine.createSpy().and.callFake(function () {
                    return true;
                });
                obj.save();
                expect(obj.source.get).toHaveBeenCalled();
            });
        });
        describe('"submit" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('submit')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.submit;

                expect(type).toEqual('function');
            });
        });
        describe('"validate" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('validate')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.validate;

                expect(type).toEqual('function');
            });
        });
        describe('"reset" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('reset')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.reset;

                expect(type).toEqual('function');
            });
        });
    });
});
