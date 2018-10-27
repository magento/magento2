/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/components/collection'
], function (_, registry, Constr) {
    'use strict';

    describe('Magento_Ui/js/form/components/collection', function () {

        var obj = new Constr({
            provider: 'provName',
            name: '',
            index: ''
        });

        registry.set('provName', {
            /** Stub */
            on: function () {},

            /** Stub */
            get: function () {},

            /** Stub */
            set: function () {}
        });

        describe('"initElement" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initElement')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.initElement;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called with object arguments', function () {
                var arg = {
                    /** Stub */
                    initContainer: function () {},

                    /** Stub */
                    activate: function () {}
                };

                expect(obj.initElement(arg)).toBeDefined();
            });
            it('Check returned value type if method called object arguments', function () {
                var arg = {
                        /** Stub */
                        initContainer: function () {},

                        /** Stub */
                        activate: function () {}
                    },
                    type = typeof obj.initElement(arg);

                expect(type).toEqual('object');
            });
            it('Check call method "this.bubble" inner initElement method', function () {
                var arg = {
                    /** Stub */
                    initContainer: function () {},

                    /** Stub */
                    activate: function () {}
                };

                obj.bubble = jasmine.createSpy();
                obj.initElement(arg);
                expect(obj.bubble).toHaveBeenCalled();
            });
        });
        describe('"initChildren" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initChildren')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.initChildren;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.initChildren()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.initChildren();

                expect(type).toEqual('object');
            });
            it('Check call method "this.source.get" inner initElement method', function () {
                obj.source.get = jasmine.createSpy();
                obj.initChildren();
                expect(obj.source.get).toHaveBeenCalled();
            });
            it('Check this.initialItems property affter called initChildren', function () {
                obj.initialItems = null;
                obj.initChildren();
                expect(obj.initialItems).toEqual([]);
            });
        });
        describe('"addChild" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('addChild')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.addChild;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called with arguments', function () {
                expect(obj.addChild()).toBeDefined();
            });
            it('Check returned value type if method called with arguments', function () {
                var type = typeof obj.addChild();

                expect(type).toEqual('object');
            });
            it('Check this.childIndex property affter called addChild', function () {
                obj.childIndex = null;
                obj.addChild('4');
                expect(obj.childIndex).toEqual('4');
            });
        });
        describe('"hasChanged" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('hasChanged')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.hasChanged;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.hasChanged()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.hasChanged();

                expect(type).toEqual('boolean');
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
            it('Check returned value if method called without arguments', function () {
                expect(obj.validate()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                expect(obj.validate()).toEqual([]);
            });
        });
        describe('"removeAddress" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('removeAddress')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.removeAddress;

                expect(type).toEqual('function');
            });
        });
    });
});
