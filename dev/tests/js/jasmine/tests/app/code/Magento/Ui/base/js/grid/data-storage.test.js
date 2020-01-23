/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
/*jscs:disable requireCamelCaseOrUpperCaseIdentifiers*/
define([
    'mageUtils',
    'Magento_Ui/js/grid/data-storage'
], function (utils, DataStorage) {
    'use strict';

    describe('Magento_Ui/js/grid/data-storage', function () {

        describe('constructor', function () {
            it('converts dataScope property to array', function () {
                var model = new DataStorage({
                    dataScope: 'magento'
                });

                expect(model.dataScope).toEqual(['magento']);
            });
        });

        describe('"initConfig" method', function () {

            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('Check for defined ', function () {
                expect(model.hasOwnProperty('initConfig')).toBeDefined();
            });

            it('Check method type', function () {
                var type = typeof model.initConfig;

                expect(type).toEqual('function');
            });

            it('Check returned value if method called without arguments', function () {
                expect(model.initConfig()).toBeDefined();
            });

            it('Check returned value type if method called without arguments', function () {
                var type = typeof model.initConfig();

                expect(type).toEqual('object');
            });

            it('Check this.dataScope property (is modify in initConfig method)', function () {
                model.dataScope = null;
                model.initConfig();
                expect(typeof model.dataScope).toEqual('object');
            });

            it('Check this._requests property (is modify in initConfig method)', function () {
                model._requests = null;
                model.initConfig();
                expect(typeof model._requests).toEqual('object');
            });
        });

        describe('"getByIds"', function() {

            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('check for defined', function() {
                expect(model.hasOwnProperty('getByIds')).toBeDefined();
            });

            it('check method type', function () {
                expect(typeof model.getByIds).toEqual('function');
            });

            it('Check returned value if method called with argument', function () {
                var ids = [1,2,3];
                expect(model.getByIds(ids)).toBeDefined();
            });

            it('check returned false if method called with argument', function() {
                var ids = [1,2,3];
                var type = typeof model.getByIds(ids);
                expect(type).toEqual('boolean');
            });

            it('Return false', function() {
                var ids = [1,2,3];
                expect(model.getByIds(ids)).toEqual('false');
            });

        });

        describe('hasScopeChanged', function () {
            it('is function', function () {
                var model = new DataStorage({
                    dataScope: ''
                });

                expect(model.hasScopeChanged).toBeDefined();
                expect(typeof model.hasScopeChanged).toEqual('function');
            });

            it('returns false if no requests have been made', function () {
                var model = new DataStorage({
                    dataScope: ''
                });

                expect(model.hasScopeChanged()).toBeFalsy();
            });

            it('tells whether parameters defined in the dataScope property have changed', function () {
                var params, newParams, model;

                params = {
                    namespace: 'magento',
                    search: '',
                    filters: {
                        store_id: 0
                    },
                    sorting: {},
                    paging: {}
                };

                newParams = utils.extend({}, params, {
                    search: 'magento',
                    filters: {
                        store_id: 1
                    }
                });

                model = new DataStorage({
                    dataScope: 'filters.store_id'
                });

                model.cacheRequest({
                    totalRecords: 0
                }, params);

                expect(model.hasScopeChanged(params)).toBeFalsy();
                expect(model.hasScopeChanged(newParams)).toBeTruthy();
            });
        });
        describe('"getRequestData" method', function () {
            var model = new DataStorage({
                dataScope: 'magento'
            });
            it('Check for defined ', function () {
                expect(model.hasOwnProperty('getRequestData')).toBeDefined();
            });

            it('Check method type', function () {
                var type = typeof model.getRequestData;

                expect(type).toEqual('function');
            });

            it('check "getRequestData" has been executed', function () {
                var request = {
                    ids: [1,2,3]
                };
                expect(model.getRequestData(request)).toBeTruthy();
            });
        });

        describe('"cacheRequest" method', function () {
            var model = new DataStorage({
                dataScope: 'magento'
            });
            it('Check for defined ', function () {
                expect(model.hasOwnProperty('cacheRequest')).toBeDefined();
            });

            it('Check method type', function () {
                var type = typeof model.cacheRequest;

                expect(type).toEqual('function');
            });

            it('check "cacheRequest" has been executed', function () {
                var data = {
                        items: [1,2,3],
                        totalRecords: 3,
                        errorMessage: ''
                    },
                    params = {
                        namespace: 'magento',
                        search: '',
                        sorting: {},
                        paging: {}
                    };
                expect(model.cacheRequest(data, params)).toBeTruthy();
            });
        });
    });
});
