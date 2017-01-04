/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'Magento_Ui/js/grid/data-storage',
], function (DataStorage) {
    'use strict';

    describe('Magento_Ui/js/grid/data-storage', function () {
        var obj = new DataStorage({
                dataScope: '',
            }),
            type;

        describe('"initConfig" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initConfig')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.initConfig;
                expect(type).toEqual('function');
            });
            it('Check method change "$this.dataScope" property', function () {
                var model = new DataStorage({dataScope: 'magento'});

                model.initConfig;
                expect(model.dataScope).toEqual(['magento']);
            });
        });
        describe('"hasScopeChanged" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('hasScopeChanged')).toBeDefined();
            });
            it('Check method type', function () {
                type = typeof obj.hasScopeChanged;
                expect(type).toEqual('function');
            });
            it('Check method with empty cached requests', function () {
                var expectedResult;

                expectedResult = obj.hasScopeChanged();
                expect(expectedResult).toBeFalsy();
            });
            it('Check method with not empty cached requests', function () {
                var expectedResult, params, requestParams, model;

                params = {
                    namespace: "magento",
                    search: "",
                    filters: {
                        store_id: 0
                    },
                    sorting: {},
                    paging: {}
                };
                requestParams = {
                    namespace: "magento",
                    search: "magento",
                    filters: {
                        store_id: 1
                    },
                    sorting: {},
                    paging: {}
                };
                model = new DataStorage(
                    {
                        dataScope: ['filters.store_id'] //became after initConfig method call
                    }
                );
                spyOn(model, "getRequest").and.returnValue({
                    ids: [],
                    params: {
                        namespace: "magento",
                        search: "",
                        filters: {
                            store_id: 0
                        },
                        sorting: {},
                        paging: {}
                    },
                    totalRecords: 0
                });
                spyOn(model, "removeRequest").and.callFake(function () {
                    return false;
                });
                model.cacheRequest({totalRecords: 0}, params);
                expect(model.getRequest).toHaveBeenCalled();
                expect(model.removeRequest).toHaveBeenCalled();
                expectedResult = model.hasScopeChanged(requestParams);
                expect(expectedResult).toBeTruthy();
            });
        });
    });
});
