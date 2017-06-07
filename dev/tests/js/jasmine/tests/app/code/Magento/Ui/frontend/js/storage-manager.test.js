

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
            'Magento_Catalog/js/product/storage/storage-service': {
                createStorage: jasmine.createSpy().and.returnValue({
                    get: jasmine.createSpy()
                })
            }
        },
        obj;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Catalog/js/storage-manager'], function (Constr) {
            obj = new Constr({
                provider: 'provName',
                name: '',
                index: '',
                links: '',
                listens: '',
                storagesConfiguration: []
            });
            done();
        });
    });

    describe('Magento_Catalog/js/storage-manager', function () {
        describe('"initStorages" method', function () {
            beforeEach(function () {
                obj.storagesConfiguration = {
                    first: {
                        savePrevious: false
                    },
                    second: {
                        savePrevious: false
                    }
                };
                obj.storagesNamespace = _.keys(obj.storagesConfiguration);
            });

            it('create new storage', function () {
                obj.initStorages();

                expect(typeof obj[obj.storagesNamespace[0]]).toBe('object');
                expect(typeof obj[obj.storagesNamespace[1]]).toBe('object');
                expect(typeof obj[obj.storagesNamespace[0]].previous).toBe('undefined');
                expect(typeof obj[obj.storagesNamespace[1]].previous).toBe('undefined');
            });
            it('create new storage with saving previous', function () {
                obj.storagesConfiguration.first.savePrevious = true;
                obj.storagesConfiguration.second.savePrevious = true;

                obj.initStorages();

                expect(typeof obj[obj.storagesNamespace[0]].previous).toBe('object');
                expect(typeof obj[obj.storagesNamespace[1]].previous).toBe('object');
            });
            it('check returned value', function () {
                expect(obj.initStorages()).toBe(obj);
            });
        });
        describe('"initStartData" method', function () {
            it('check start data', function () {
                obj.updateDataHandler = jasmine.createSpy();
                obj.storagesNamespace = ['first', 'second'];
                obj[obj.storagesNamespace[0]] = obj[obj.storagesNamespace[1]] = {
                    get: jasmine.createSpy()
                };

                obj.initStartData();
                expect(obj.updateDataHandler).toHaveBeenCalledWith('first', obj[obj.storagesNamespace[0]].get());
                expect(obj.updateDataHandler).toHaveBeenCalledWith('second', obj[obj.storagesNamespace[1]].get());
            });
            it('check returned value', function () {
                expect(obj.initStartData()).toBe(obj);
            });
        });
        describe('"prepareStoragesConfig" method', function () {
            beforeEach(function () {
                obj.storagesConfiguration = {
                    first: {
                        savePrevious: false,
                        requestConfig: {
                            url: '/path/'
                        }
                    },
                    second: {
                        savePrevious: false,
                        requestConfig: {
                            url: '/path/'
                        }
                    }
                };
                obj.requestConfig = {
                    additionalData: 'data'
                };
            });

            it('check storagesNamespace', function () {
                obj.prepareStoragesConfig();
                expect(obj.storagesNamespace[0]).toBe('first');
                expect(obj.storagesNamespace[1]).toBe('second');
            });
            it('check storage requestConfig', function () {
                obj.prepareStoragesConfig();
                expect(obj.storagesConfiguration.first.requestConfig.additionalData).toBe('data');
                expect(obj.storagesConfiguration.second.requestConfig.additionalData).toBe('data');
            });
            it('check returned value', function () {
                expect(obj.prepareStoragesConfig()).toBe(obj);
            });
        });
        describe('"getUtcTime" method', function () {
            it('check type of returned value', function () {
                expect(typeof obj.getUtcTime()).toBe('number');
            });
        });
        describe('"initUpdateStorageDataListener" method', function () {
            it('check type of returned value', function () {
                obj.storagesNamespace = ['first', 'second'];
                obj.first = {
                    data: {
                        subscribe: jasmine.createSpy()
                    }
                };
                obj.second = {
                    data: {
                        subscribe: jasmine.createSpy()
                    }
                };

                expect(typeof obj.getUtcTime()).toBe('number');
            });
        });
        describe('"updateDataHandler" method', function () {
            var data = {
                    property: 'value'
                },
                name = 'first',
                value = 'value',
                lastUpdate = 1300000000,
                utcTime = 1500000000,
                lastUpdatePeriod = 100000000;

            beforeEach(function () {
                obj.getLastUpdate = jasmine.createSpy().and.returnValue(lastUpdate);
                obj.getUtcTime = jasmine.createSpy().and.returnValue(utcTime);
                obj.lastUpdatePeriod = lastUpdatePeriod;
                obj.dataFilter = jasmine.createSpy().and.returnValue(value);
                obj.sendRequest = jasmine.createSpy();
            });

            it('check calling "getLastUpdate", "getUtcTime"', function () {
                obj[name] = {};
                obj.updateDataHandler(name, {});

                expect(obj.getLastUpdate).toHaveBeenCalledWith('first');
                expect(obj.getUtcTime).toHaveBeenCalled();
            });
            it('check calls with data and without previous data', function () {
                obj[name] = {
                    set: jasmine.createSpy()
                };
                obj.updateDataHandler(name, data);

                expect(obj.dataFilter).toHaveBeenCalledWith(data, utcTime, name);
                expect(obj.first.set).toHaveBeenCalledWith(value);
                expect(obj.sendRequest).toHaveBeenCalledWith(name, value);
            });
            it('check calls without data and previous data', function () {
                obj[name] = {
                    set: jasmine.createSpy()
                };
                obj.updateDataHandler(name);

                expect(obj.dataFilter).not.toHaveBeenCalled();
                expect(obj.first.set).not.toHaveBeenCalledWith();
                expect(obj.sendRequest).not.toHaveBeenCalledWith();
            });
            it('check calls with data and without previous data', function () {
                obj[name] = {
                    set: jasmine.createSpy(),
                    previous: {
                        set: jasmine.createSpy(),
                        get: jasmine.createSpy()
                    }
                };
                obj.updateDataHandler(name, data);

                expect(obj.dataFilter).toHaveBeenCalledWith(data, utcTime, name);
                expect(obj.first.set).toHaveBeenCalledWith(value);
                expect(obj.sendRequest).toHaveBeenCalledWith(name, value);
                expect(obj.first.previous.get).toHaveBeenCalled();
                expect(obj.first.previous.set).not.toHaveBeenCalled();
            });
            it('check calls with data that equal with previous data', function () {
                obj[name] = {
                    set: jasmine.createSpy(),
                    previous: {
                        set: jasmine.createSpy(),
                        get: jasmine.createSpy().and.returnValue(data)
                    }
                };
                obj.updateDataHandler(name, data);

                expect(obj.dataFilter).not.toHaveBeenCalled();
                expect(obj.first.set).not.toHaveBeenCalled();
                expect(obj.sendRequest).not.toHaveBeenCalled();
                expect(obj.first.previous.get).toHaveBeenCalled();
                expect(obj.first.previous.set).not.toHaveBeenCalled();
            });
        });
        describe('"getLastUpdate" method', function () {
            beforeEach(function () {
                window.localStorage.getItem = jasmine.createSpy().and.returnValue('value');
            });

            it('check calling "getItem" method of localStorage', function () {
                var name = 'first';

                obj[name] = {
                    namespace: 'namespace'
                };

                expect(obj.getLastUpdate(name)).toBe('value');
                expect(window.localStorage.getItem).toHaveBeenCalledWith(obj[name].namespace + '_last_update');
            });
        });
        describe('"setLastUpdate" method', function () {
            beforeEach(function () {
                window.localStorage.setItem = jasmine.createSpy().and.returnValue('value')
            });

            it('check calling "setItem" method of localStorage', function () {
                var name = 'first',
                    utcTime = 1500000000;

                obj[name] = {
                    namespace: 'namespace'
                };
                obj.getUtcTime = jasmine.createSpy().and.returnValue(utcTime);

                obj.setLastUpdate(name);
                expect(window.localStorage.setItem).toHaveBeenCalledWith(obj[name].namespace + '_last_update', utcTime);
            });
        });
        describe('"dataFilter" method', function () {
            var data = [
                    {
                        'added_at': 1400000000,
                        'product_id': 0
                    },
                    {
                        'added_at': 1200000000,
                        'product_id': 1
                    }
                ],
                currentTime = 1500000000,
                name = 'first';

            beforeEach(function () {
                obj.storagesConfiguration = {
                    first: {
                        lifetime: 200000000
                    }
                }
            });

            it('check result of "dataFilter" method', function () {
                var result = obj.dataFilter(data, currentTime, name);

                expect(result[0]).toBe(data[0]);
                expect(result[1]).toBe(undefined);
            });
        });
    });
});
