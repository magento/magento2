/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'squire'
], function ($, Squire) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_Catalog/js/product/storage/data-storage': {},
            'Magento_Catalog/js/product/storage/ids-storage-compare': {}
        },
        obj,
        utils;

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Magento_Catalog/js/product/storage/storage-service', function () {
        var config = {
                namespace: 'namespace',
                className: 'IdsStorage'
            },
            storage;

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Catalog/js/product/storage/ids-storage',
                'Magento_Catalog/js/product/storage/storage-service',
                'mageUtils'
            ], function (IdsStorage, instance, mageUtils) {
                obj = instance;
                utils = mageUtils;
                done();
            });
        });

        describe('"createStorage" method', function () {
            it('create new storage', function () {
                obj.processSubscribers = jasmine.createSpy();
                storage = obj.createStorage(config);

                expect(obj.processSubscribers).toHaveBeenCalled();
                expect(typeof storage).toBe('object');
            });
        });
        describe('"processSubscribers" and "onStorageInit" method', function () {
            var callback = jasmine.createSpy();

            beforeEach(function () {
                obj.onStorageInit('IdsStorage', callback);
            });

            it('test "processsubscribers" and "onStorageInit" method by proxy "createStorage" method', function () {
                obj.onStorageInit(config.namespace, callback);
                obj.createStorage(config);
                expect(callback).toHaveBeenCalled();
            });
        });
        describe('"getStorage" method', function () {
            it('test returned value', function () {
                obj.createStorage(config);
                obj.getStorage(config.namespace);

                expect(typeof obj.getStorage(config.namespace)).toBe('object');
            });
        });
        describe('"add" method', function () {
            var storageValue;

            beforeEach(function () {
                storage = new obj.createStorage(config);
                storageValue = {
                    'property1': 1
                };

                storage.set(storageValue);
            });

            it('method exists', function () {
                expect(storage.add).toBeDefined();
                expect(typeof storage.add).toEqual('function');
            });

            it('update value', function () {
                spyOn(utils, 'copy').and.callThrough();
                expect(storage.get()).toEqual(storageValue);

                storageValue = {
                    'property2': 2
                };

                storage.add(storageValue);

                expect(utils.copy).toHaveBeenCalled();
                expect(storage.get()).toEqual(
                    {
                        'property1': 1,
                        'property2': 2
                    }
                );
            });

            it('add empty value', function () {
                spyOn(utils, 'copy').and.callThrough();

                storage.add({});

                expect(utils.copy).not.toHaveBeenCalled();
                expect(storage.get()).toEqual(
                    {
                        'property1': 1
                    }
                );
            });
        });
    });
});
