/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* global jQuery */
/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'squire',
    'underscore'
], function ($, Squire, _) {
    'use strict';


    var injector = new Squire(),
        mocks = {
            'Magento_Catalog/js/product/storage/ids-storage': {
                name: 'IdsStorage',
                initialize: jasmine.createSpy().and.returnValue({})
            },
            'Magento_Catalog/js/product/storage/data-storage': {},
            'Magento_Catalog/js/product/storage/ids-storage-compare': {}
        },
        obj;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Catalog/js/product/storage/storage-service'], function (insance) {
            obj = insance;
            done();
        });
    });

    describe('Magento_Catalog/js/product/storage/storage-service', function () {
        var config = {
                namespace: 'namespace',
                className: 'IdsStorage'
            },
            storage;

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
    });
});
