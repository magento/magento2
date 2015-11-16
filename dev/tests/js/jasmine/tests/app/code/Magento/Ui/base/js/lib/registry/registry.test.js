/**
 * Copyright © 2015 Magento. All rights reserved.
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
            it('Check assigned value after used method', function () {
                var elem = 'test',
                    prop = 'magento';
                
                registry.set(elem, prop);
                expect(registry.storage.data.get(elem)).toEqual(prop);
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
            it('Check called callback with arguments', function () {
                var elems = ['magento'],
                    callback = function () {};

                registry.events.wait = jasmine.createSpy();
                registry.get(elems, callback);
                expect(registry.events.wait).toHaveBeenCalledWith(elems, callback);
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
            it('Check called registry.storage.remove with arguments', function () {
                var elems = ['magento'];

                registry.storage.remove = jasmine.createSpy();
                registry.remove(elems);
                expect(registry.storage.remove).toHaveBeenCalledWith(elems);
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
            it('Check returned value if registry.storage has property', function () {
                var name = 'magento';

                registry.storage.data.set(name, 'magentoValue');
                expect(registry.has(name)).toEqual(true);
            });
            it('Check returned value if registry.storage has not property', function () {
                var name = 'magentoNonProperty';

                expect(registry.has(name)).toEqual(false);
            });
            it('Check called registry.storage.has with arguments', function () {
                var elems = ['magento'];

                registry.storage.has = jasmine.createSpy();
                registry.has(elems);
                expect(registry.storage.has).toHaveBeenCalledWith(elems);
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
            it('Check registry.storage for defined', function () {
                registry.create();
                expect(registry.storage).toBeDefined();
            });
            it('Check registry.storage type', function () {
                registry.create();
                expect(typeof registry.storage).toEqual('object');
            });
            it('Check registry.events for defined', function () {
                registry.create();
                expect(registry.events).toBeDefined();
            });
            it('Check registry.events type', function () {
                registry.create();
                expect(typeof registry.events).toEqual('object');
            });
        });
    });
});
