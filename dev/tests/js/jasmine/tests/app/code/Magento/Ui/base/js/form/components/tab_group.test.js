/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/components/tab_group'
], function (_, registry, Constr) {
    'use strict';

    describe('Magento_Ui/js/form/components/tab_group', function () {

        var obj = new Constr({
            provider: 'provName',
            name: '',
            index: ''
        });

        window.FORM_KEY = 'magentoFormKey';

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
                    /** Stub */
                    initContainer: function () {},

                    /** Stub */
                    on: function () {},

                    /** Stub */
                    active: function () {},

                    /** Stub */
                    activate: function () {}
                };

                expect(obj.initElement(arg)).toBeDefined();
            });
            it('Check returned value type if method called with object argument', function () {
                var arg = {
                        /** Stub */
                        initContainer: function () {},

                        /** Stub */
                        on: function () {},

                        /** Stub */
                        active: function () {},

                        /** Stub */
                        activate: function () {}
                    },
                    type = typeof obj.initElement(arg);

                expect(type).toEqual('object');
            });
        });
        describe('"initActivation" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initActivation')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.initActivation;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called with object argument', function () {
                var arg = {
                    /** Stub */
                    initContainer: function () {},

                    /** Stub */
                    on: function () {},

                    /** Stub */
                    active: function () {},

                    /** Stub */
                    activate: function () {}
                };

                expect(obj.initActivation(arg)).toBeDefined();
            });
            it('Check returned value type if method called with object argument', function () {
                var arg = {
                        /** Stub */
                        initContainer: function () {},

                        /** Stub */
                        on: function () {},

                        /** Stub */
                        active: function () {},

                        /** Stub */
                        activate: function () {}
                    },
                    type = typeof obj.initActivation(arg);

                expect(type).toEqual('object');
            });
        });
        describe('"validate" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('validate')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.validate;

                expect(type).toEqual('function');
            });
            it('Check called "argument.delegate" inner validate method', function () {
                var arg = {
                    /** Stub */
                    initContainer: function () {},

                    /** Stub */
                    on: function () {},

                    /** Stub */
                    active: function () {},

                    /** Stub */
                    activate: function () {},
                    delegate: jasmine.createSpy()
                };

                obj.validate(arg);
                expect(arg.delegate).toHaveBeenCalled();
            });
        });
        describe('"onValidate" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('onValidate')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.onValidate;

                expect(type).toEqual('function');
            });
            it('Check called "this.elems.sortBy" inner onValidate method', function () {
                obj.elems.sortBy = jasmine.createSpy().and.callFake(function () {
                    return [];
                });
                obj.onValidate();
                expect(obj.elems.sortBy).toHaveBeenCalled();
            });
            it('Check called "this.validate" in onValidate and count calls', function () {
                obj.elems.sortBy = jasmine.createSpy().and.callFake(function () {
                    return ['1', '2', '3'];
                });
                obj.validate = jasmine.createSpy().and.callFake(function () {
                    return [];
                });
                obj.onValidate();
                expect(obj.validate.calls.count()).toBe(1);
            });
        });
    });
});
