/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* global jQuery */
/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'squire'
], function ($, Squire) {
    'use strict';

    var injector = new Squire(),
        mocks = {},
        obj;

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Catalog/js/product/storage/ids-storage'], function (insance) {
            obj = insance;
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {}
    });

    describe('Magento_Catalog/js/product/storage/ids-storage', function () {
        describe('"getDataFromLocalStorage" method', function () {
            it('check calls localStorage get method', function () {
                obj.localStorage = {
                    get: jasmine.createSpy()
                };

                obj.getDataFromLocalStorage();
                expect(obj.localStorage.get).toHaveBeenCalled();
            });
        });
        describe('"cachesDataFromLocalStorage" method', function () {
            it('check calls localStorage get method', function () {
                obj.getDataFromLocalStorage = jasmine.createSpy().and.returnValue({});

                expect(obj.localStorage.get).toHaveBeenCalled();
            });
        });
        describe('"initLocalStorage" method', function () {
            it('check returned value', function () {
                obj.namespace = 'test';
                obj.initLocalStorage();

                expect(typeof obj.localStorage).toBe('object');
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
        describe('"internalDataHandler" method', function () {
            var data = {
                property: 'value'
            };

            beforeEach(function () {
                obj.localStorage = {
                    get: jasmine.createSpy().and.returnValue(data),
                    set: jasmine.createSpy()
                };
            });

            it('check calls with data that equal with data in localStorage', function () {
                obj.internalDataHandler(data);

                expect(obj.localStorage.get).toHaveBeenCalled();
                expect(obj.localStorage.set).not.toHaveBeenCalled();
            });

            it('check calls with data that not equal with data in localStorage', function () {
                var emptyData = {};

                obj.internalDataHandler(emptyData);

                expect(obj.localStorage.get).toHaveBeenCalled();
                expect(obj.localStorage.set).toHaveBeenCalledWith(emptyData);
            });
        });
        describe('"externalDataHandler" method', function () {
            var data = {
                firstProperty: 'firstValue'
            };

            beforeEach(function () {
                obj.data = jasmine.createSpy().and.returnValue({
                    secondProperty: 'secondValue'
                });

                /**
                 * Mock for set method
                 */
                obj.set = function (param) {
                    this.result = param;
                };
            });

            it('check calls with data', function () {
                obj.externalDataHandler(data);

                expect(obj.data).toHaveBeenCalled();
                expect(obj.result.firstProperty).toBe('firstValue');
                expect(obj.result.secondProperty).toBe('secondValue');
            });
        });
    });
});
