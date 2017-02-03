/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'Magento_Ui/js/lib/registry/storage'
], function (Storage) {
    'use strict';

    describe('Magento_Ui/js/lib/registry/storage', function () {
        var storage = new Storage();
        describe('"Storage constructor"', function () {
            it('Check for defined', function () {
                expect(storage).toBeDefined();
            });
            it('Check type', function () {
                var type = typeof storage;

                expect(type).toEqual('object');
            });
            it('Check storage.data for defined', function () {
                var data = storage.data;

                expect(typeof data).toEqual('object');
            });
        });
        describe('"storage.get" method', function () {
            it('Check for defined', function () {
                expect(storage.hasOwnProperty('get')).toBeDefined();
            });
            it('Check type', function () {
                var type = typeof storage.get;

                expect(type).toEqual('function');
            });
            it('Check returned value if argument is array values', function () {
                var elem = 'magento',
                    value = 'magentoValue';

                storage.data.set(elem, value);
                expect(storage.get([elem])).toEqual([value]);
            });
            it('Check returned value if called withot arguments', function () {
                expect(storage.get()).toEqual([]);
            });
        });
        describe('"storage.set" method', function () {
            it('Check for defined', function () {
                expect(storage.hasOwnProperty('set')).toBeDefined();
            });
            it('Check type', function () {
                var type = typeof storage.set;

                expect(type).toEqual('function');
            });
            it('Check returned value for defined', function () {
                expect(storage.set()).toBeDefined();
            });
            it('Check returned value type', function () {
                var type = typeof storage.set();

                expect(type).toEqual('object');
            });
            it('Check returned value if argument is "elem, value" ', function () {
                var elem = 'magento',
                    value = 'magentoValue';

                storage.set(elem, value);
                expect(storage.data.get(elem)).toEqual(value);
            });
        });
        describe('"storage.remove" method', function () {
            it('Check for defined', function () {
                expect(storage.hasOwnProperty('remove')).toBeDefined();
            });
            it('Check type', function () {
                var type = typeof storage.remove;

                expect(type).toEqual('function');
            });
            it('Check returned value for defined', function () {
                expect(storage.remove([])).toBeDefined();
            });
            it('Check returned value type', function () {
                var type = typeof storage.remove([]);

                expect(type).toEqual('object');
            });
            it('Check if called with argument "elem" ', function () {
                var elem = 'magento',
                    value = 'magentoValue';

                storage.data.set(elem, value);
                storage.remove([elem]);
                expect(storage.data.get(elem)).not.toBeDefined();
            });
        });
        describe('"storage.has" method', function () {
            it('Check for defined', function () {
                expect(storage.hasOwnProperty('has')).toBeDefined();
            });
            it('Check type', function () {
                var type = typeof storage.has;

                expect(type).toEqual('function');
            });
            it('Check returned value if data has element property', function () {
                var elem = 'magento',
                    value = 'magentoValue';

                storage.data.set(elem, value);
                expect(storage.has([elem])).toEqual(true);
            });
            it('Check returned value if data has not element property', function () {
                expect(storage.has(['value'])).toEqual(false);
            });
        });
    });
});
