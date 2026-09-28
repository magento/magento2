/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire',
    'underscore'
], function (Squire, _) {
    'use strict';

    var injector = new Squire(),
        obj,
        customerDataIds,
        localStorageIds,
        timestamp,
        namespace = 'namespace',
        windowCheckoutData = window.checkout,
        customerDataGet = function () {
            return {
                items: customerDataIds
            };
        },
        customerData = {
            get: jasmine.createSpy().and.returnValue(customerDataGet)
        },
        productResolverIds = [],
        productResolver = jasmine.createSpy().and.callFake(function () {
            return productResolverIds;
        }),
        storage = {
            onStorageInit: jasmine.createSpy(),
            createStorage: jasmine.createSpy()
        },
        mocks = {
            'Magento_Customer/js/customer-data': customerData,
            'Magento_Catalog/js/product/view/product-ids-resolver': productResolver,
            'Magento_Catalog/js/product/storage/storage-service': storage
        };

    beforeEach(function (done) {
        injector.mock(mocks);
        injector.require(['Magento_Catalog/js/product/provider'], function (UiClass) {
            timestamp = new Date().getTime() / 1000;
            obj = new UiClass({
                identifiersConfig: {
                    namespace: namespace
                },
                ids: {
                    'website-1-1': {
                        added_at: timestamp - 300,
                        product_id: 1,
                        scope_id: 1
                    }
                },
                data: {
                    store: '1',
                    currency: 'USD',
                    productCurrentScope: 'website'
                }
            });
            localStorageIds = {
                'website-1-2': {
                    added_at: timestamp - 60,
                    product_id: 2,
                    scope_id: 1
                },
                'website-1-3': {
                    added_at: timestamp - 180,
                    product_id: 3,
                    scope_id: 1
                }
            };
            customerDataIds = {
                4: {
                    added_at: timestamp - 360,
                    product_id: 4,
                    scope_id: 1
                }
            };
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
        } catch (e) {
        }
        window.localStorage.clear();
        window.checkout = windowCheckoutData;
    });

    describe('Magento_Catalog/js/product/provider', function () {
        describe('"_resolveDataByIds" method', function () {
            beforeEach(function () {
                obj.initIdsListener = jasmine.createSpy();
                obj.idsMerger = jasmine.createSpy();
                obj.idsHandler = jasmine.createSpy();
                obj.filterIds = jasmine.createSpy().and.returnValue({});
            });
            it('check "window.checkout" is required', function () {
                window.checkout = undefined;
                obj.ids = jasmine.createSpy();

                obj._resolveDataByIds();

                expect(obj.ids).not.toHaveBeenCalled();
                expect(obj.filterIds).not.toHaveBeenCalled();
                expect(obj.idsHandler).not.toHaveBeenCalled();
                expect(obj.initIdsListener).not.toHaveBeenCalled();
                expect(obj.idsMerger).not.toHaveBeenCalled();
            });
            it('check that initial ids, localstorage ids and ids from customer data are processed', function () {
                var initialIds = obj.ids();

                window.checkout = {
                    baseUrl: 'http://localhost/',
                    websiteId: 1
                };
                obj.idsStorage = {
                    get: jasmine.createSpy().and.returnValue(localStorageIds),
                    lifetime: 1000
                };
                obj.prepareDataFromCustomerData = jasmine.createSpy().and.returnValue(customerDataIds);
                customerData.get = jasmine.createSpy().and.returnValue(customerDataGet);

                obj._resolveDataByIds();

                expect(obj.filterIds).toHaveBeenCalledOnceWith(initialIds);
                expect(obj.idsHandler).toHaveBeenCalledOnceWith({});
                expect(obj.initIdsListener).toHaveBeenCalled();
                expect(customerData.get).toHaveBeenCalledOnceWith(namespace);
                expect(obj.prepareDataFromCustomerData).toHaveBeenCalledOnceWith({items: customerDataIds});
                expect(obj.idsMerger).toHaveBeenCalledOnceWith(localStorageIds, customerDataIds);
            });
        });
        describe('"idsMerger" method', function () {
            beforeEach(function () {
                obj.idsHandler = jasmine.createSpy();
                obj.filterIds = jasmine.createSpy().and.returnValue({});
            });
            it('check merge empty', function () {
                obj.idsMerger({}, {});

                expect(obj.filterIds).not.toHaveBeenCalled();
                expect(obj.idsHandler).not.toHaveBeenCalled();
            });

            it('check merge not empty', function () {
                var initialIds = obj.ids();

                obj.idsMerger(localStorageIds, {});

                expect(obj.filterIds).toHaveBeenCalledOnceWith(_.extend({}, initialIds, localStorageIds));
                expect(obj.idsHandler).toHaveBeenCalledOnceWith({});
            });
        });

        describe('"prepareDataFromCustomerData" method', function () {
            it('argument is empty', function () {
                expect(obj.prepareDataFromCustomerData({})).toEqual({});
            });

            it('argument is an object and has "items" property', function () {
                expect(obj.prepareDataFromCustomerData({items: customerDataIds})).toEqual(customerDataIds);
            });

            it('argument is an object and does not have "items" property', function () {
                expect(obj.prepareDataFromCustomerData(customerDataIds)).toEqual(customerDataIds);
            });
        });

        describe('"filterIds" method', function () {
            beforeEach(function () {
                window.checkout = {
                    websiteId: 1
                };
                obj.idsStorage = {
                    lifetime: 1000
                };
            });

            it('filters out "out of scope" ids', function () {
                window.checkout.websiteId = 2;
                expect(obj.filterIds(localStorageIds)).toEqual({});
            });

            it('filters out expired ids', function () {
                obj.idsStorage.lifetime = 100;
                expect(obj.filterIds(localStorageIds)).toEqual({2: localStorageIds['website-1-2']});
            });

            it('filters out current product id', function () {
                productResolverIds.push(2);
                expect(obj.filterIds(localStorageIds)).toEqual({3: localStorageIds['website-1-3']});
            });
        });
    });
});
