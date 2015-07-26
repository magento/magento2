/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/client'
], function (_, registry, constr) {
    'use strict';

    describe('Magento_Ui/js/form/client', function () {
        registry.set('provName', {
            on: function () {
            },
            get: function () {
            },
            set: function () {
            }
        });

        var obj = new constr({
            provider: 'provName',
            name: '',
            index: ''
        });
        describe('"save" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('save')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof(obj.save);

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                obj.urls = {};
                obj.urls.beforeSave = {};
                expect(obj.save()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof(obj.save());

                expect(type).toEqual('object');
            });
        });
        describe('"initialize" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initialize')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof(obj.initialize);

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.initialize()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof(obj.initialize());

                expect(type).toEqual('object');
            });
        });
    });
});
