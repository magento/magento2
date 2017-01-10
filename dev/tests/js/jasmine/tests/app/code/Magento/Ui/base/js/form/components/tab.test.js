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
    'Magento_Ui/js/form/components/tab'
], function (_, registry, Constr) {
    'use strict';

    describe('Magento_Ui/js/form/components/tab', function () {
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
        describe('"activate" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('activate')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.activate;

                expect(type).toEqual('function');
            });
            it('Check called "this.setUnique" inner activate method', function () {
                obj.setUnique = jasmine.createSpy().and.callFake(function () {
                    return obj;
                });
                obj.activate();
                expect(obj.setUnique).toHaveBeenCalled();
            });
            it('Check observable variable "active" after execution activate method', function () {
                obj.activate();
                expect(obj.active()).toEqual(true);
            });
            it('Check observable variable "wasActivated" after execution activate method', function () {
                obj.activate();
                expect(obj.wasActivated()).toEqual(true);
            });
        });
    });
});
