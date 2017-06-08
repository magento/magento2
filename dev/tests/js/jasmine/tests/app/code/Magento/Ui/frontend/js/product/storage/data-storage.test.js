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

        /**
         * Mock for customerData get method
         */
        customerDataGet = function () {
            return {
                customerDataGet: 'customerDataGetValue'
            };
        },
        mocks = {
            'Magento_Customer/js/customer-data': {
                get: jasmine.createSpy().and.returnValue(customerDataGet)
            },
            'Magento_Catalog/js/product/query-builder': {
                buildQuery: jasmine.createSpy().and.returnValue({})
            }
        },
        obj;

    /**
     * Mock for customerData subscribe method
     */
    customerDataGet.subscribe = jasmine.createSpy();

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Catalog/js/product/storage/data-storage'], function (insance) {
            obj = _.extend({}, insance);
            done();
        });
    });

    describe('Magento_Catalog/js/product/storage/data-storage', function () {
        describe('"initCustomerDataInvalidateListener" method', function () {
            it('check returned value', function () {
                expect(obj.initCustomerDataInvalidateListener()).toBe(obj);
            });
        });
        describe('"initDataListener" method', function () {
            it('check calls "subscribe"', function () {
                obj.data = {
                    subscribe: jasmine.createSpy()
                };
                obj.initDataListener();

                expect(obj.data.subscribe).toHaveBeenCalled();
            });
        });
        describe('"initProvideStorage" method', function () {
            beforeEach(function () {
                obj.providerHandler = jasmine.createSpy();
            });

            it('check calls "providerHandler" method', function () {
                obj.initProvideStorage();
                expect(obj.providerHandler).toHaveBeenCalledWith(customerDataGet());
            });
            it('check returned value', function () {
                expect(obj.initProvideStorage()).toBe(obj);
            });
        });
        describe('"dataHandler" method', function () {
            beforeEach(function () {
                obj.localStorage = {
                    removeAll: jasmine.createSpy(),
                    set: jasmine.createSpy()
                };
            });

            it('check calls "dataHandler" method with data', function () {
                var data = {
                    property: 'value'
                };

                obj.dataHandler(data);
                expect(obj.localStorage.set).toHaveBeenCalledWith(data);
                expect(obj.localStorage.removeAll).not.toHaveBeenCalled();
            });
            it('check calls "dataHandler" method with empty data', function () {
                obj.dataHandler({});
                expect(obj.localStorage.set).not.toHaveBeenCalled();
                expect(obj.localStorage.removeAll).toHaveBeenCalled();
            });
        });
        describe('"providerHandler" method', function () {
            beforeEach(function () {
                obj.localStorage = {
                    removeAll: jasmine.createSpy(),
                    set: jasmine.createSpy()
                };

                /**
                 * Mock for observable data property
                 */
                obj.data = function (data) {
                    if (!data) {
                        return {
                            dataProperty: 'dataValue'
                        };
                    }

                    this.result = data;
                };
            });

            it('check calls "providerHandler" method with data', function () {
                var data = {
                    items: {
                        key: 'value'
                    }
                };

                obj.providerHandler(data);

                expect(obj.result.key).toBe('value');
                expect(obj.result.dataProperty).toBe('dataValue');
            });
            it('check calls "providerHandler" method without data', function () {
                obj.providerHandler({});

                expect(obj.result).toBe(undefined);
            });
        });
        describe('"setIds" method', function () {
            var currency = 'currency',
                store = '1',
                ids = '4';

            beforeEach(function () {
                obj.data = {
                    valueHasMutated: jasmine.createSpy()
                };
                obj.loadDataFromServer = jasmine.createSpy();
            });

            it('check calls "setIds" method without data in cache', function () {
                obj.hasInCache = jasmine.createSpy().and.returnValue(false);
                obj.setIds(currency, store, ids);

                expect(obj.hasInCache).toHaveBeenCalledWith(currency, store, ids);
                expect(obj.loadDataFromServer).toHaveBeenCalledWith(currency, store, ids);
                expect(obj.data.valueHasMutated).not.toHaveBeenCalled();
            });
            it('check calls "setIds" method with data in cache', function () {
                obj.hasInCache = jasmine.createSpy().and.returnValue(true);
                obj.setIds(currency, store, ids);

                expect(obj.hasInCache).toHaveBeenCalledWith(currency, store, ids);
                expect(obj.loadDataFromServer).not.toHaveBeenCalled();
                expect(obj.data.valueHasMutated).toHaveBeenCalled();
            });
        });
        describe('"getDataByIdentifiers" method', function () {
            var currency = 'currency',
                store = '1',
                productIdentifiers = {
                    '1': {
                        id: '1'
                    },
                    '2': {
                        id: '2'
                    }
                };

            beforeEach(function () {
                obj.data = jasmine.createSpy().and.returnValue({
                    '1': {
                        value: 'firstValue'
                    },
                    '2': {
                        value: 'secondValue'
                    }
                });
            });

            it('check calls "getDataByIdentifiers" with productIdentifiers', function () {
                var result = obj.getDataByIdentifiers(currency, store, productIdentifiers);

                expect(obj.data).toHaveBeenCalled();
                expect(result['1'].value).toBe('firstValue');
                expect(result['2'].value).toBe('secondValue');
            });
            it('check calls "getDataByIdentifiers" without productIdentifiers', function () {
                var result = obj.getDataByIdentifiers(currency, store, {});

                expect(obj.data).toHaveBeenCalled();
                expect(typeof result).toBe('object');
                expect(_.isEmpty(result)).toBe(true);
            });
        });
        describe('"hasInCache" method', function () {
            var currency = 'currency',
                store = '1';

            beforeEach(function () {
                obj.data = jasmine.createSpy().and.returnValue({
                    '1': {
                        value: 'firstValue'
                    },
                    '2': {
                        value: 'secondValue'
                    }
                });
            });

            it('check calls "hasInCache" with ids that exists in data', function () {
                var ids  = {
                        '1': {
                            id: '1'
                        },
                        '2': {
                            id: '2'
                        }
                    },
                    result = obj.hasInCache(currency, store, ids);

                expect(obj.data).toHaveBeenCalled();
                expect(result).toBe(true);
            });
            it('check calls "hasInCache" with ids that does not exists in data', function () {
                var ids  = {
                        '5': {
                            id: '5'
                        },
                        '6': {
                            id: '6'
                        }
                    },
                    result = obj.hasInCache(currency, store, ids);

                expect(obj.data).toHaveBeenCalled();
                expect(result).toBe(false);
            });
        });
        describe('"loadDataFromServer" method', function () {
            var currency = 'currency',
                store = '1',
                ids  = {
                    '1': {
                        id: '1'
                    },
                    '2': {
                        id: '2'
                    }
                };

            beforeEach(function () {
                obj.updateRequestConfig = {};
                obj.hasIdsInSentRequest = jasmine.createSpy();
                obj.request = {
                    sent: true
                };
                obj.data = jasmine.createSpy().and.returnValue({
                    '1': {
                        value: 'firstValue'
                    },
                    '2': {
                        value: 'secondValue'
                    }
                });
            });

            it('check calls "loadDataFromServer" method inside "loadDataFromServer"', function () {
                obj.loadDataFromServer(currency, store, ids);

                expect(obj.hasIdsInSentRequest).toHaveBeenCalled();
            });

            it('check calls "hasInCache" with ids that exists in data', function () {
                obj.loadDataFromServer(currency, store, ids);

                expect(typeof obj.request).toBe('object');
            });

            it('check data in "updateRequestConfig" property', function () {
                obj.loadDataFromServer(currency, store, ids);

                expect(obj.updateRequestConfig.data['store_id']).toBe(store);
                expect(obj.updateRequestConfig.data['currency_code']).toBe(currency);
            });
        });
        describe('"hasIdsInSentRequest" method', function () {
            var ids  = {
                    '1': {
                        id: '1'
                    },
                    '2': {
                        id: '2'
                    }
                };

            beforeEach(function () {
                obj.request = {
                    data: {
                        '1': {
                            data: 'value'
                        },
                        '2': {
                            data: 'value'
                        }
                    }
                };
            });

            it('check calls "hasIdsInSentRequest" with empty request data', function () {
                obj.request = {};

                expect(obj.hasIdsInSentRequest(ids)).toBe(false);
            });

            it('check calls "hasIdsInSentRequest" with request data', function () {
                expect(obj.hasIdsInSentRequest(ids)).toBe(true);
            });
        });
        describe('"addDataFromPageCache" method', function () {
            beforeEach(function () {
                obj.providerHandler = jasmine.createSpy();
            });

            it('check calls "addDataFromPageCache" inside "addDataFromPageCache" method', function () {
                obj.addDataFromPageCache({});

                expect(obj.providerHandler).toHaveBeenCalled();
            });
        });
        describe('"initProviderListener" method', function () {
            beforeEach(function () {
                obj.providerHandler = jasmine.createSpy();
            });

            it('check returned value', function () {
                obj.initProviderListener();

                expect(obj.initProviderListener()).toBe(obj);
                expect(typeof obj.initProviderListener()).toBe('object');
            });
        });
        describe('"cachesDataFromLocalStorage" method', function () {
            it('check calls "getDataFromLocalStorage" and "data" method', function () {
                var data = {};

                obj.getDataFromLocalStorage = jasmine.createSpy().and.returnValue(data);
                obj.data = jasmine.createSpy();
                obj.cachesDataFromLocalStorage();

                expect(obj.getDataFromLocalStorage).toHaveBeenCalled();
                expect(obj.data).toHaveBeenCalledWith(data);
            });
        });
        describe('"getDataFromLocalStorage" method', function () {
            it('check calls localStorage get method', function () {
                obj.localStorage = {
                    get: jasmine.createSpy()
                };

                obj.getDataFromLocalStorage();
                expect(obj.localStorage.get).toHaveBeenCalled();
            });
        });
        describe('"initLocalStorage" method', function () {
            it('check localStorage', function () {
                obj.namespace = 'test';
                obj.initLocalStorage();

                expect(typeof obj.localStorage).toBe('object');
            });
            it('check returned value', function () {
                obj.namespace = 'test';

                expect(typeof obj.initLocalStorage()).toBe('object');
                expect(obj.initLocalStorage()).toBe(obj);
            });
        });
    });
});
