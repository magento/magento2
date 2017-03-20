/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'uiRegistry'
], function (registry) {
    'use strict';

    describe('Magento_Ui/js/lib/registry/registry', function () {
        describe('"registry" object', function () {
            it('Check for defined ', function () {
                expect(registry).toBeDefined();
            });
            it('Check type', function () {
                var type = typeof registry;

                expect(type).toEqual('object');
            });
        });
        describe('"registry.set" method', function () {
            it('Check for defined', function () {
                expect(registry.hasOwnProperty('set')).toBeDefined();
            });
            it('Check type', function () {
                var type = typeof registry.set;

                expect(type).toEqual('function');
            });
            it('Check returned value', function () {
                expect(registry.set()).toBeDefined();
            });
            it('Check returned value type', function () {
                var type = typeof registry.set();

                expect(type).toEqual('object');
            });
        });
        describe('"registry.get" method', function () {
            it('Check for defined', function () {
                expect(registry.hasOwnProperty('get')).toBeDefined();
            });
            it('Check type', function () {
                var type = typeof registry.get;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(registry.get()).toBeUndefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = registry.get() instanceof Array;

                expect(type).toBeFalsy();
            });
        });
        describe('"registry.remove" method', function () {
            it('Check for defined', function () {
                expect(registry.hasOwnProperty('remove')).toBeDefined();
            });
            it('Check type', function () {
                var type = typeof registry.remove;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called with arguments', function () {
                expect(registry.remove('magento')).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof registry.remove('magento');

                expect(type).toEqual('object');
            });
        });
        describe('"registry.has" method', function () {
            it('Check for defined', function () {
                expect(registry.hasOwnProperty('has')).toBeDefined();
            });
            it('Check type', function () {
                var type = typeof registry.has;

                expect(type).toEqual('function');
            });
            it('Check returned value if registry.storage has not property', function () {
                var name = 'magentoNonProperty';

                expect(registry.has(name)).toEqual(false);
            });
        });
        describe('"registry.async" method', function () {
            it('Check for defined', function () {
                expect(registry.hasOwnProperty('async')).toBeDefined();
            });
            it('Check type', function () {
                var type = typeof registry.async;

                expect(type).toEqual('function');
            });
        });
        describe('"registry.create" method', function () {
            it('Check for defined', function () {
                expect(registry.hasOwnProperty('create')).toBeDefined();
            });
            it('Check type', function () {
                var type = typeof registry.async;

                expect(type).toEqual('function');
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof registry.remove('magento');

                expect(type).toEqual('object');
            });
        });
    });
});
