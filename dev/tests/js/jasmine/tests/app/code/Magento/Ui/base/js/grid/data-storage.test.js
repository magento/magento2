/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
/*jscs:disable requireCamelCaseOrUpperCaseIdentifiers*/
define([
    'jquery',
    'mageUtils',
    'underscore',
    'Magento_Ui/js/grid/data-storage'
], function ($, utils, _, DataStorage) {
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

        describe('"getByIds" method', function () {

            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('check returned type if method called with argument', function () {
                var ids = [1,2,3],
                    type = typeof model.getByIds(ids);

                expect(type).toEqual('boolean');
            });

            it('Return false if "getByIds" has been called', function () {
                var ids = [1,2,3];

                expect(model.getByIds(ids)).toEqual(false);
            });

            it('Return array if "getByIds" has been called', function () {
                var ids = [1],
                    expectedValue = [
                        {
                            id_field_name: 'entity_id',
                            entity_id: '1'
                        }
                    ];

                model = new DataStorage({
                    dataScope: 'magento',
                    data: {
                        1: {
                            id_field_name: 'entity_id',
                            entity_id: '1'
                        }
                    }
                });

                expect(model.getByIds(ids)).toEqual(expectedValue);
            });

        });

        describe('"getIds" method', function () {

            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('check array of entity_id will return', function () {
                var ids = [
                    {
                        id_field_name: 'entity_id',
                        entity_id: '1'
                    }
                ],
                expectedValue = ['1'];
                expect(model.getIds(ids)).toEqual(expectedValue);
            });

        });

        describe('"getData" method', function () {

            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('check returned type if method called with argument', function () {
                var params = {
                    namespace: 'magento',
                    search: '',
                    filters: {
                        store_id: 0
                    },
                    sorting: {},
                    paging: {}
                },
                    type = typeof model.getData(params);

                expect(type).toEqual('object');
            });

            it('check "clearRequests" has been called', function () {
                var params = {
                    namespace: 'magento',
                    search: '',
                    filters: {
                        store_id: 0
                    },
                    sorting: {},
                    paging: {}
                };

                spyOn(model, 'clearRequests');
                spyOn(model, 'hasScopeChanged').and.callFake(function () {
                    return true;
                });
                model.getData(params);
                expect(model.clearRequests).toHaveBeenCalled();
            });

            it('check "getRequest" has been called', function () {
                var params = {
                    namespace: 'magento',
                    search: '',
                    filters: {
                        store_id: 0
                    },
                    sorting: {},
                    paging: {}
                };

                spyOn(model, 'getRequest');
                spyOn(model, 'hasScopeChanged').and.callFake(function () {
                    return false;
                });
                model.getData(params);
                expect(model.getRequest).toHaveBeenCalled();
            });

            it('it returns cached request data if a cached request exists and no refresh option is provided', function () {
                var params = {
                    namespace: 'magento',
                    search: '',
                    filters: {
                        store_id: 0
                    },
                    sorting: {},
                    paging: {}
                },
                    options = {
                        refresh: false
                    };

                spyOn(model, 'getRequestData');
                spyOn(model, 'getRequest').and.callFake(function () {
                    return true;
                });
                model.getData(params, options);
                expect(model.getRequestData).toHaveBeenCalled();
            });

            it('if refresh option is true so it will ignore cache and execute the requestData function', function () {
                var params = {
                        namespace: 'magento',
                        search: '',
                        filters: {
                            store_id: 0
                        },
                        sorting: {},
                        paging: {}
                    },
                    options = {
                        refresh: true
                    };

                spyOn(model, 'requestData');
                spyOn(model, 'getRequest').and.callFake(function () {
                    return false;
                });
                model.getData(params, options);
                expect(model.requestData).toHaveBeenCalled();
            });

        });

        describe('"hasScopeChanged" method', function () {
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

        describe('"updateData" method', function () {
            var model = new DataStorage({
                dataScope: 'magento',
                requestConfig: {
                    url: 'magento.com',
                    method: 'GET',
                    dataType: 'json'
                }
            });

            it('Check updateData has been called', function () {
                var data = [{
                    id_field_name: 'entity_id',
                    entity_id: '1'
                }];

                expect(model.updateData(data)).toBeTruthy();
            });
        });

        describe('"requestData" method', function () {
            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('Check Ajax request', function () {
                var params = {
                    namespace: 'magento',
                    search: '',
                    filters: {
                        store_id: 0
                    },
                    sorting: {},
                    paging: {}
                },
                query = utils.copy(params);

                spyOn(model, 'onRequestComplete');
                spyOn($, 'ajax').and.callFake(function () {
                    return {
                        /**
                         * Success result for ajax request
                         */
                        done: function () {
                            model.onRequestComplete(model, query);
                        }
                    };
                });
                model.requestData(params);
                expect($.ajax).toHaveBeenCalled();
                expect(model.onRequestComplete).toHaveBeenCalled();
            });
        });

        describe('"getRequest" method', function () {
            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('check "getRequest" has been executed', function () {
                var params = {
                    namespace: 'magento',
                    search: '',
                    sorting: {},
                    paging: {}
                };

                model._requests.push({
                    ids: ['1'],
                    params: params,
                    totalRecords: 1,
                    errorMessage: ''
                });
                expect(model.getRequest(params)).toBeTruthy();
            });
        });

        describe('"getRequestData" method', function () {
            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('check "getRequestData" has been executed', function () {
                var request = {
                    ids: [1,2,3]
                };

                expect(model.getRequestData(request)).toBeTruthy();
            });

            it('check "getByIds" has been executed', function () {
                var request = {
                    ids: [1,2,3]
                };

                spyOn(model, 'getByIds');
                model.getRequestData(request);
                expect(model.getByIds).toHaveBeenCalled();
            });

            it('check "delay" function has been executed', function () {
                var request = {
                    ids: [1,2,3],
                    totalRecords: 3,
                    errorMessage: ''
                };

                spyOn(_, 'delay');
                model.getRequestData(request);
                expect(_.delay).toHaveBeenCalled();
            });

            it('check "delay" function has not been executed', function () {
                var request = {
                        ids: [1,2,3],
                        totalRecords: 3,
                        errorMessage: ''
                    };
                model = new DataStorage({
                    dataScope: 'magento',
                    cachedRequestDelay: 0
                });
                spyOn(_, 'delay');
                model.getRequestData(request);
                expect(_.delay).not.toHaveBeenCalled();
            });
        });

        describe('"cacheRequest" method', function () {
            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('check "model._requests"', function () {
                var params = {
                    namespace: 'magento',
                    search: '',
                    sorting: {},
                    paging: {}
                },
                data = {
                    items: ['1','2','3'],
                    totalRecords: 3
                };

                spyOn(model, 'removeRequest');
                spyOn(model, 'getIds').and.callFake(function () {
                    return ['1','2','3'];
                });
                model.cacheRequest(data, params);
                expect(typeof model._requests).toEqual('object');
                expect(model.getIds).toHaveBeenCalled();
                expect(model.removeRequest).not.toHaveBeenCalled();
            });

            it('check "removeRequest" is executed', function () {
                var params = {
                        namespace: 'magento',
                        search: '',
                        sorting: {},
                        paging: {}
                    },
                    data = {
                        items: ['1','2','3'],
                        totalRecords: 3
                    };

                spyOn(model, 'removeRequest');
                spyOn(model, 'getRequest').and.callFake(function () {
                    return true;
                });
                spyOn(model, 'getIds').and.callFake(function () {
                    return ['1','2','3'];
                });
                model.cacheRequest(data, params);
                expect(typeof model._requests).toEqual('object');
                expect(model.getIds).toHaveBeenCalled();
                expect(model.removeRequest).toHaveBeenCalled();
            });
        });

        describe('"clearRequests" method', function () {

            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('check "clearRequests" will empty _requests array', function () {
                var params = {
                    namespace: 'magento',
                    search: 'magento',
                    filters: {
                        store_id: 1
                    }
                };

                model = new DataStorage({
                    dataScope: 'magento',
                    _requests: []
                });

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

            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('check "removeRequest" is defined', function () {
                var params = {
                    namespace: 'magento',
                    search: 'magento',
                    filters: {
                        store_id: 1
                    }
                },
                request = [{
                    ids: [1,2,3],
                    params: params,
                    totalRecords: 3,
                    errorMessage: 'errorMessage'
                }];

                expect(model.removeRequest(request)).toBeDefined();
            });
        });

        describe('"wasRequested" method', function () {
            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('Return false if getRequest method returns false', function () {
                var params = {
                    namespace: 'magento',
                    search: '',
                    sorting: {},
                    paging: {}
                };

                spyOn(model, 'getRequest').and.callFake(function () {
                    return false;
                });
                expect(model.wasRequested(params)).toBeFalsy();
            });
        });

        describe('"onRequestComplete" method', function () {
            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('Check "updateData" method has been called', function () {
                var data = {
                    items: [{
                        id_field_name: 'entity_id',
                        entity_id: '1'
                    }]
                },
                params = {
                    namespace: 'magento',
                    search: '',
                    sorting: {},
                    paging: {}
                };

                spyOn(model, 'updateData').and.callFake(function () {
                    return data;
                });
                model.onRequestComplete(params, data);
                expect(model.updateData).toHaveBeenCalled();
            });

            it('Check "cacheRequest" method has been called', function () {
                var data = {
                    items: [{
                        id_field_name: 'entity_id',
                        entity_id: '1'
                    }]
                },
                params = {
                    namespace: 'magento',
                    search: '',
                    sorting: {},
                    paging: {}
                };

                model = new DataStorage({
                    dataScope: 'magento',
                    cacheRequests: true
                });
                spyOn(model, 'cacheRequest').and.callFake(function () {
                    return data;
                });
                model.onRequestComplete(params, data);
                expect(model.cacheRequest).toHaveBeenCalled();
            });
        });
    });
});
