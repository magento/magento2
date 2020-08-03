/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
/*jscs:disable requireCamelCaseOrUpperCaseIdentifiers*/
define([
    'jquery',
    'Magento_Ui/js/grid/data-storage'
], function ($, DataStorage) {
    'use strict';

    describe('Magento_Ui/js/grid/data-storage', function () {

        describe('"initConfig" method', function () {

            it('returns self', function () {
                var model = new DataStorage({
                    dataScope: 'magento'
                });

                expect(model.initConfig()).toEqual(model);
            });

            it('changes string dataScope property to an array', function () {
                var model = new DataStorage({
                    dataScope: 'magento'
                });

                expect(model.dataScope).toEqual(['magento']);
            });

            it('changes empty string dataScope property to an empty array', function () {
                var model = new DataStorage({
                    dataScope: ''
                });

                expect(model.dataScope).toEqual([]);
            });

            it('doesn\'t change non-string dataScope property', function () {
                var testScope = {
                        testKey: 'test value'
                    },
                    model = new DataStorage({
                        dataScope: testScope
                    });

                expect(model.dataScope).toEqual(testScope);
            });

            it('initializes _requests property as an empty array', function () {
                var model = new DataStorage();

                model._requests = null;
                model.initConfig();
                expect(model._requests).toEqual([]);
            });
        });

        describe('"getByIds" method', function () {

            it('returns false if data for ids is missing', function () {
                var model = new DataStorage();

                expect(model.getByIds([1,2,3])).toEqual(false);
            });

            it('returns array of items', function () {
                var item = {
                        id_field_name: 'entity_id',
                        entity_id: '1'
                    },
                    model = new DataStorage({
                        data: {
                            1: item
                        }
                    });

                expect(model.getByIds([1])).toEqual([item]);
            });

        });

        describe('"getIds" method', function () {

            it('returns an array of entity_id\'s from provided data', function () {
                var model = new DataStorage(),
                    ids = [
                        {
                            id_field_name: 'entity_id',
                            entity_id: '1'
                        },
                        {
                            id_field_name: 'entity_id',
                            entity_id: '54'
                        }
                    ];

                expect(model.getIds(ids)).toEqual(['1', '54']);
            });

            it('returns an array of entity_id\'s from stored data if no arguments provided', function () {
                var model = new DataStorage({
                        data: {
                            1: {
                                id_field_name: 'entity_id',
                                entity_id: '1'
                            },
                            2: {
                                id_field_name: 'entity_id',
                                entity_id: '42'
                            }
                        }
                    });

                expect(model.getIds()).toEqual(['1', '42']);
            });

        });

        describe('"getData" method', function () {

            var model = new DataStorage();

            it('returns the result of requestData method if scope have been changed', function () {
                var requestDataResult = 'requestDataResult';

                spyOn(model, 'clearRequests');
                spyOn(model, 'hasScopeChanged').and.returnValue(true);
                spyOn(model, 'requestData').and.returnValue(requestDataResult);
                spyOn(model, 'getRequest');
                expect(model.getData()).toEqual(requestDataResult);
                expect(model.clearRequests).toHaveBeenCalled();
                expect(model.getRequest).not.toHaveBeenCalled();
            });

            it('returns the cached result if scope have not been changed', function () {
                var cachedRequestDataResult = 'cachedRequestDataResult';

                spyOn(model, 'clearRequests');
                spyOn(model, 'requestData');
                spyOn(model, 'hasScopeChanged').and.returnValue(false);
                spyOn(model, 'getRequest').and.returnValue(true);
                spyOn(model, 'getRequestData').and.returnValue(cachedRequestDataResult);

                expect(model.getData()).toEqual(cachedRequestDataResult);
                expect(model.clearRequests).not.toHaveBeenCalled();
                expect(model.requestData).not.toHaveBeenCalled();
            });

            it('returns the result of requestData method if refresh option is provided', function () {
                var requestDataResult = 'requestDataResult',
                    options = {
                        refresh: true
                    };

                spyOn(model, 'getRequest').and.returnValue(true);
                spyOn(model, 'clearRequests');
                spyOn(model, 'hasScopeChanged').and.returnValue(true);
                spyOn(model, 'requestData').and.returnValue(requestDataResult);
                expect(model.getData({}, options)).toEqual(requestDataResult);
                expect(model.clearRequests).toHaveBeenCalled();
            });

        });

        describe('"hasScopeChanged" method', function () {

            it('returns false if no requests have been made', function () {
                var model = new DataStorage();

                expect(model.hasScopeChanged()).toBeFalsy();
            });

            it('returns true for not cached params', function () {
                var params = {
                        search: '1',
                        filters: {
                            store_id: 0
                        }
                    },
                    newParams = {
                        search: '2',
                        filters: {
                            store_id: 1
                        }
                    },
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

        describe('"updateData" method', function () {
            var model = new DataStorage({
                dataScope: 'magento',
                requestConfig: {
                    url: 'magento.com',
                    method: 'GET',
                    dataType: 'json'
                },
                data: {
                    1: {
                        id_field_name: 'entity_id',
                        entity_id: '1',
                        field: 'value'
                    }
                }
            });

            it('updates data items', function () {
                var data = [{
                    id_field_name: 'entity_id',
                    entity_id: '1',
                    field: 'updatedValue'
                }];

                expect(model.updateData(data)).toEqual(model);
                expect(model.getByIds([1])).toEqual(data);
            });
        });

        describe('"requestData" method', function () {
            var model = new DataStorage();

            it('Check Ajax request', function () {
                var result = 'result';

                spyOn(model, 'onRequestComplete').and.returnValue(result);
                spyOn($, 'ajax').and.returnValue({
                    /**
                     * Success result for ajax request
                     *
                     * @param {Function} handler
                     * @returns {*}
                     */
                    done: function (handler) {
                        return handler();
                    }
                });
                expect(model.requestData({})).toEqual(result);
            });
        });

        describe('"getRequest" method', function () {
            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('returns cached request', function () {
                var params = {
                        namespace: 'magento',
                        search: '',
                        sorting: {},
                        paging: {}
                    },
                    request = {
                        ids: ['1'],
                        params: params,
                        totalRecords: 1,
                        errorMessage: ''
                    };

                model._requests.push(request);
                expect(model.getRequest(params)).toEqual(request);
            });
        });

        describe('"getRequestData" method', function () {
            it('returns request data', function () {
                var request = {
                        ids: [1,2],
                        totalRecords: 2,
                        errorMessage: ''
                    },
                    items = [
                        {
                            id_field_name: 'entity_id',
                            entity_id: '1'
                        },
                        {
                            id_field_name: 'entity_id',
                            entity_id: '2'
                        }
                    ],
                    result = {
                        items: items,
                        totalRecords: 2,
                        errorMessage: ''
                    },
                    model = new DataStorage({
                        cachedRequestDelay: 0
                    });

                spyOn(model, 'getByIds').and.returnValue(items);
                model.getRequestData(request).then(function (promiseResult) {
                    expect(promiseResult).toEqual(result);
                });
            });
        });

        describe('"cacheRequest" method', function () {
            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('adds the request to the cache', function () {
                var params = {
                        namespace: 'magento',
                        search: '',
                        sorting: {},
                        paging: {}
                    },
                    ids = ['1','2','3'],
                    data = {
                        items: ids,
                        totalRecords: 3,
                        errorMessage: ''
                    },
                    request = {
                        ids: ids,
                        params: params,
                        totalRecords: 3,
                        errorMessage: ''
                    };

                spyOn(model, 'removeRequest');
                spyOn(model, 'getIds').and.returnValue(ids);
                model.cacheRequest(data, params);
                expect(model.getRequest(params)).toEqual(request);
                expect(model.removeRequest).not.toHaveBeenCalled();
            });

            it('overwrites the previously cached request for the same params', function () {
                var params = {
                        namespace: 'magento',
                        search: '',
                        sorting: {},
                        paging: {}
                    },
                    ids = ['1','2','3'],
                    firstData = {
                        items: ids,
                        totalRecords: 3,
                        errorMessage: ''
                    },
                    secondData = {
                        items: ids,
                        totalRecords: 3,
                        errorMessage: 'Error message'
                    },
                    firstRequest = {
                        ids: ids,
                        params: params,
                        totalRecords: 3,
                        errorMessage: ''
                    },
                    secondRequest = {
                        ids: ids,
                        params: params,
                        totalRecords: 3,
                        errorMessage: 'Error message'
                    };

                spyOn(model, 'getIds').and.returnValue(ids);
                model.cacheRequest(firstData, params);
                expect(model.getRequest(params)).toEqual(firstRequest);
                model.cacheRequest(secondData, params);
                expect(model.getRequest(params)).toEqual(secondRequest);
            });
        });

        describe('"clearRequests" method', function () {

            it('removes all cached requests', function () {
                var model = new DataStorage(),
                    params = {
                        namespace: 'magento',
                        search: 'magento',
                        filters: {
                            store_id: 1
                        }
                    };

                model._requests.push({
                    ids: ['1','2','3','4'],
                    params: params,
                    totalRecords: 4,
                    errorMessage: 'errorMessage'
                });
                model.clearRequests();
                expect(model._requests).toEqual([]);
            });
        });

        describe('"removeRequest" method', function () {

            var model = new DataStorage();

            it('removes the request from the cache', function () {
                var params = {
                        namespace: 'magento',
                        search: '',
                        sorting: {},
                        paging: {}
                    },
                    request = {
                        ids: ['1','2','3'],
                        params: params,
                        totalRecords: 3,
                        errorMessage: ''
                    };

                model._requests = [request];
                expect(model.getRequest(params)).toEqual(request);
                model.removeRequest(request);
                expect(model.getRequest(params)).toBeFalsy();
            });
        });

        describe('"wasRequested" method', function () {
            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('returns false if request is not present in cache', function () {
                var params = {
                    namespace: 'magento',
                    search: '',
                    sorting: {},
                    paging: {}
                };

                expect(model.wasRequested(params)).toBeFalsy();
            });

            it('returns true if request is present in cache', function () {
                var params = {
                    namespace: 'magento',
                    search: '',
                    sorting: {},
                    paging: {}
                },
                request = {
                    ids: ['1','2','3'],
                    params: params,
                    totalRecords: 3,
                    errorMessage: ''
                };

                model._requests = [request];

                expect(model.wasRequested(params)).toBeTruthy();
            });
        });

        describe('"onRequestComplete" method', function () {

            it('updates data and does not cache the request if caching is disabled', function () {
                var model = new DataStorage({
                        cacheRequests: false
                    }),
                    data = {
                        items: []
                    },
                    params = {};

                spyOn(model, 'updateData');
                spyOn(model, 'cacheRequest');
                model.onRequestComplete(params, data);
                expect(model.updateData).toHaveBeenCalled();
                expect(model.cacheRequest).not.toHaveBeenCalled();
            });

            it('updates data and adds the request to cache if caching is enabled', function () {
                var model = new DataStorage({
                        cacheRequests: true
                    }),
                    data = {
                        items: []
                    },
                    params = {};

                spyOn(model, 'updateData');
                spyOn(model, 'cacheRequest');
                model.onRequestComplete(params, data);
                expect(model.updateData).toHaveBeenCalled();
                expect(model.cacheRequest).toHaveBeenCalled();
            });
        });
    });
});
