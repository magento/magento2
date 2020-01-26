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

        describe('"getByIds" method', function () {

            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('check for defined', function () {
                expect(model.hasOwnProperty('getByIds')).toBeDefined();
            });

            it('check method type', function () {
                expect(typeof model.getByIds).toEqual('function');
            });

            it('Check returned value if method called with argument', function () {
                var ids = [1,2,3];

                expect(model.getByIds(ids)).toBeDefined();
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
                var ids = [1];

                model = new DataStorage({
                    dataScope: 'magento',
                    data: {
                        1: {
                            id_field_name: 'entity_id',
                            entity_id: '1'
                        }
                    }
                });

                expect(typeof model.getByIds(ids)).toEqual('object');
            });

        });

        describe('"getIds" method', function () {

            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('check for defined', function () {
                expect(model.hasOwnProperty('getIds')).toBeDefined();
            });

            it('check method type', function () {
                expect(typeof model.getIds).toEqual('function');
            });

            it('check returned value if method called with argument', function () {
                var ids = [1,2,3];

                expect(model.getIds(ids)).toBeDefined();
            });

            it('check returned type if method called with argument', function () {
                var ids = [1,2,3],
                    type = typeof model.getIds(ids);

                expect(type).toEqual('object');
            });

        });

        describe('"getData" method', function () {

            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('check for defined', function () {
                expect(model.hasOwnProperty('getData')).toBeDefined();
            });

            it('check method type', function () {
                expect(typeof model.getData).toEqual('function');
            });

            it('check returned value if method called with argument', function () {
                var params = {
                    namespace: 'magento',
                    search: '',
                    filters: {
                        store_id: 0
                    },
                    sorting: {},
                    paging: {}
                };

                expect(model.getData(params)).toBeDefined();
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

            it('Return "getRequestData" method', function () {
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

            it('Return "requestData" method', function () {
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
                dataScope: 'magento'
            });

            it('Check for defined ', function () {
                expect(model.hasOwnProperty('updateData')).toBeDefined();
            });

            it('Check method type', function () {
                var type = typeof model.updateData;

                expect(type).toEqual('function');
            });

            it('Check updateData has been called', function () {
                var data = [{
                    id_field_name: 'entity_id',
                    entity_id: '1'
                }];

                expect(model.updateData(data)).toBeTruthy();
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

            it('Check for defined ', function () {
                expect(model.hasOwnProperty('clearRequests')).toBeDefined();
            });

            it('Check method type', function () {
                var type = typeof model.clearRequests;

                expect(type).toEqual('function');
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

            it('Check for defined ', function () {
                expect(model.hasOwnProperty('removeRequest')).toBeDefined();
            });

            it('Check method type', function () {
                var type = typeof model.removeRequest;

                expect(type).toEqual('function');
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

            it('Check for defined ', function () {
                expect(model.hasOwnProperty('wasRequested')).toBeDefined();
            });

            it('Check method type', function () {
                var type = typeof model.wasRequested;

                expect(type).toEqual('function');
            });

            it('Return false if getRequest method returns false', function () {
                var params = {
                    namespace: 'magento',
                    search: '',
                    sorting: {},
                    paging: {}
                };

                model.wasRequested(params);
                expect(model.wasRequested(params)).toBeFalsy();
            });
        });

        describe('"onRequestComplete" method', function () {
            var model = new DataStorage({
                dataScope: 'magento'
            });

            it('Check for defined ', function () {
                expect(model.hasOwnProperty('onRequestComplete')).toBeDefined();
            });

            it('Check method type', function () {
                var type = typeof model.onRequestComplete;

                expect(type).toEqual('function');
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
