/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */
/* jscs:disable jsDoc*/

define([
    'underscore',
    'squire',
    'jquery',
    'Magento_Customer/js/section-config',
    'Magento_Customer/js/customer-data',
    'jquery/jquery-storageapi'
], function (_, Squire, $, sectionConfig, customerData) {
    'use strict';

    var injector = new Squire(),
        obj,
        originaljQuery,
        originalGetJSON,
        originalReload,
        originalIsEmpty,
        originalEach,
        cookieLifeTime = 3600,
        sectionConfigSettings = {
            baseUrls: [
                'http://localhost/'
            ],
            sections: {
                'customer/account/loginpost': ['*'],
                'checkout/cart/add': ['cart'],
                'rest/*/v1/guest-carts/*/selected-payment-method': ['cart', 'checkout-data'],
                '*': ['messages']
            },
            clientSideSections: [
                'checkout-data',
                'cart-data'
            ],
            sectionNames: [
                'customer',
                'product_data_storage',
                'cart',
                'messages'
            ]
        };

    function init(config) {
        var defaultConfig = {
            sectionLoadUrl: 'http://localhost/customer/section/load/',
            expirableSectionLifetime: 60, // minutes
            expirableSectionNames: ['cart'],
            cookieLifeTime: cookieLifeTime,
            updateSessionUrl: 'http://localhost/customer/account/updateSession/'
        };

        customerData['Magento_Customer/js/customer-data']($.extend({}, defaultConfig, config || {}));
    }

    function setupLocalStorage(sections) {
        var mageCacheStorage = {},
            sectionDataIds = {};

        _.each(sections, function (sectionData, sectionName) {
            sectionDataIds[sectionName] = sectionData['data_id'];

            if (typeof sectionData.content !== 'undefined') {
                mageCacheStorage[sectionName] = sectionData;
            }
        });

        $.localStorage.set(
            'mage-cache-storage',
            mageCacheStorage
        );
        $.cookieStorage.set(
            'section_data_ids',
            sectionDataIds
        );

        $.localStorage.set(
            'mage-cache-timeout',
            new Date(Date.now() + cookieLifeTime * 1000)
        );
        $.cookieStorage.set(
            'mage-cache-sessid',
            true
        );
    }

    function clearLocalStorage() {
        $.cookieStorage.set('section_data_ids', {});

        if (window.localStorage) {
            window.localStorage.clear();
        }
    }

    describe('Magento_Customer/js/customer-data', function () {
        beforeAll(function () {
            clearLocalStorage();
        });

        beforeEach(function (done) {
            originalGetJSON = $.getJSON;
            sectionConfig['Magento_Customer/js/section-config'](sectionConfigSettings);

            injector.require([
                'underscore',
                'Magento_Customer/js/customer-data'
            ], function (underscore, Constr) {
                _ = underscore;
                obj = Constr;
                done();
            });
        });

        afterEach(function () {
            try {
                $.getJSON = originalGetJSON;
                clearLocalStorage();
                injector.clean();
                injector.remove();
            } catch (e) {
            }
        });

        describe('"init" method', function () {
            var storageInvalidation = {
                keys: function () {
                    return ['section'];
                }
            };

            beforeEach(function () {
                originalReload = obj.reload;
                originalIsEmpty = _.isEmpty;

                $.initNamespaceStorage('mage-cache-storage').localStorage;
                $.initNamespaceStorage('mage-cache-storage-section-invalidation').localStorage;

                spyOn(storageInvalidation, 'keys').and.returnValue(['section']);
            });

            afterEach(function () {
                obj.reload = originalReload;
                _.isEmpty = originalIsEmpty;
                $.namespaceStorages = {};
            });

            it('Should be defined', function () {
                expect(obj.hasOwnProperty('init')).toBeDefined();
            });

            it('Does not throw before component is initialized', function () {
                obj.initStorage();

                expect(function () {
                    obj.init();
                }).not.toThrow();
            });

            it('Calls "getExpiredSectionNames" method', function () {
                spyOn(obj, 'getExpiredSectionNames').and.returnValue([]);
                obj.init();
                expect(obj.getExpiredSectionNames).toHaveBeenCalled();
            });

            it('Calls "reload" method when expired sections exist', function () {
                spyOn(obj, 'getExpiredSectionNames').and.returnValue(['section']);
                spyOn(obj, 'reload').and.returnValue(true);
                obj.init();
                expect(obj.reload).toHaveBeenCalled();
            });

            it('Calls "reload" method when expired sections do not exist', function () {
                spyOn(obj, 'getExpiredSectionNames').and.returnValue([]);
                spyOn(obj, 'reload').and.returnValue(true);
                spyOn(_, 'isEmpty').and.returnValue(false);

                obj.init();
                expect(obj.reload).toHaveBeenCalled();
            });

            it('Check it does not request sections from the server if there are no expired sections', function () {
                setupLocalStorage({
                    'catalog': { // without storage content
                        'data_id': Math.floor(Date.now() / 1000) + 60 // in 1 minute
                    }
                });

                $.getJSON = jasmine.createSpy().and.callFake(function () {
                    var deferred = $.Deferred();

                    return deferred.promise();
                });

                init();
                expect($.getJSON).not.toHaveBeenCalled();
            });

            it('Check it requests sections from the server if there are expired sections', function () {
                clearLocalStorage();
                setupLocalStorage({
                    'customer': {
                        'data_id': Math.floor(Date.now() / 1000) + 60 // invalidated,
                    },
                    'cart': {
                        'data_id': Math.floor(Date.now() / 1000) - 61 * 60, // 61 minutes ago
                        'content': {}
                    },
                    'product_data_storage': {
                        'data_id': Math.floor(Date.now() / 1000) + 60, // in 1 minute
                        'content': {}
                    },
                    'catalog': {
                        'data_id': Math.floor(Date.now() / 1000) + 60 // invalid section,
                    },
                    'checkout': {
                        'data_id': Math.floor(Date.now() / 1000) - 61 * 60, // invalid section,
                        'content': {}
                    }
                });

                $.getJSON = jasmine.createSpy('$.getJSON').and.callFake(function () {
                    var deferred = $.Deferred();

                    return deferred.promise();
                });

                init();
                expect($.getJSON).toHaveBeenCalledWith(
                    'http://localhost/customer/section/load/',
                    jasmine.objectContaining({
                        sections: 'cart,customer'
                    })
                );
            });
        });

        describe('"getExpiredSectionNames method', function () {
            it('Should be defined', function () {
                expect(obj.hasOwnProperty('getExpiredSectionNames')).toBeDefined();
            });

            it('Does not throw before component is initialized', function () {
                expect(function () {
                    obj.getExpiredSectionNames();
                }).not.toThrow();
            });

            it('Check that result contains expired section names', function () {
                setupLocalStorage({
                    'cart': {
                        'data_id': Math.floor(Date.now() / 1000) - 61 * 60, // 61 minutes ago
                        'content': {}
                    }
                });

                $.getJSON = jasmine.createSpy('$.getJSON').and.callFake(function () {
                    var deferred = $.Deferred();

                    return deferred.promise();
                });

                init();
                expect(customerData.getExpiredSectionNames()).toEqual(['cart']);
            });

            it('Check that result does not contain unexpired section names', function () {
                setupLocalStorage({
                    'cart': {
                        'data_id': Math.floor(Date.now() / 1000) + 60, // in 1 minute
                        'content': {}
                    }
                });
                init();
                expect(customerData.getExpiredSectionNames()).toEqual([]);
            });

            it('Check that result contains invalidated section names', function () {
                clearLocalStorage();
                setupLocalStorage({
                    'cart': { // without storage content
                        'data_id': Math.floor(Date.now() / 1000) + 60 // in 1 minute
                    }
                });

                $.getJSON = jasmine.createSpy('$.getJSON').and.callFake(function () {
                    var deferred = $.Deferred();

                    return deferred.promise();
                });

                init();
                expect(customerData.getExpiredSectionNames()).toEqual(['cart']);
            });

            it('Check that result does not contain unsupported section names', function () {
                setupLocalStorage({
                    'catalog': { // without storage content
                        'data_id': Math.floor(Date.now() / 1000) + 60 // in 1 minute
                    }
                });

                init();
                expect(customerData.getExpiredSectionNames()).toEqual([]);
            });
        });

        describe('"get" method', function () {
            it('Should be defined', function () {
                expect(obj.hasOwnProperty('get')).toBeDefined();
            });

            it('Does not throw before component is initialized', function () {
                expect(function () {
                    obj.get();
                }).not.toThrow();
            });
        });

        describe('"set" method', function () {
            beforeEach(function () {
                originalEach = _.each;
            });

            afterEach(function () {
                _.each = originalEach;
            });

            it('Should be defined', function () {
                expect(obj.hasOwnProperty('set')).toBeDefined();
            });

            it('Does not throw before component is initialized', function () {
                _.each = jasmine.createSpy().and.returnValue(true);

                expect(function () {
                    obj.set();
                }).not.toThrow();
            });
        });

        describe('"reload" method', function () {
            beforeEach(function () {
                originaljQuery = $;
                $ = jQuery;

                $.getJSON = jasmine.createSpy().and.callFake(function () {
                    var deferred = $.Deferred();

                    /**
                     * Mock Done Method for getJSON
                     * @returns object
                     */
                    deferred.promise().done = function () {
                        return {
                            responseJSON: {
                                section: {}
                            }
                        };
                    };

                    return deferred.promise();
                });
            });

            afterEach(function () {
                $ = originaljQuery;
            });

            it('Should be defined', function () {
                expect(obj.hasOwnProperty('reload')).toBeDefined();
            });

            it('Does not throw before component is initialized', function () {
                expect(function () {
                    obj.reload();
                }).not.toThrow();
            });

            it('Check it returns proper sections object when passed array with a single section name', function () {
                var result;

                spyOn(sectionConfig, 'filterClientSideSections').and.returnValue(['section']);

                $.getJSON = jasmine.createSpy().and.callFake(function (url, parameters) {
                    var deferred = $.Deferred();

                    /**
                     * Mock Done Method for getJSON
                     * @returns object
                     */
                    deferred.promise().done = function () {
                        return {
                            responseJSON: {
                                section: {}
                            }
                        };
                    };
                    expect(parameters).toEqual(jasmine.objectContaining({
                        sections: 'section'
                    }));

                    return deferred.promise();
                });

                result = obj.reload(['section'], true);
                expect(result).toEqual(jasmine.objectContaining({
                    responseJSON: {
                        section: {}
                    }
                }));
            });

            it('Check it returns proper sections object when passed array with multiple section names', function () {
                var result;

                spyOn(sectionConfig, 'filterClientSideSections').and.returnValue(['cart,customer,messages']);
                $.getJSON = jasmine.createSpy().and.callFake(function (url, parameters) {
                    var deferred = $.Deferred();

                    expect(parameters).toEqual(jasmine.objectContaining({
                        sections: 'cart,customer,messages'
                    }));

                    /**
                     * Mock Done Method for getJSON
                     * @returns object
                     */
                    deferred.promise().done = function () {
                        return {
                            responseJSON: {
                                cart: {},
                                customer: {},
                                messages: {}
                            }
                        };
                    };

                    return deferred.promise();
                });

                result = obj.reload(['cart', 'customer', 'messages'], true);
                expect(result).toEqual(jasmine.objectContaining({
                    responseJSON: {
                        cart: {},
                        customer: {},
                        messages: {}
                    }
                }));
            });

            it('Check it returns all sections when passed wildcard string', function () {
                var result;

                $.getJSON = jasmine.createSpy().and.callFake(function (url, parameters) {
                    var deferred = $.Deferred();

                    expect(parameters).toEqual(jasmine.objectContaining({
                        'force_new_section_timestamp': true
                    }));

                    /**
                     * Mock Done Method for getJSON
                     * @returns object
                     */
                    deferred.promise().done = function () {
                        return {
                            responseJSON: {
                                cart: {},
                                customer: {},
                                messages: {}
                            }
                        };
                    };

                    return deferred.promise();
                });

                result = obj.reload('*', true);
                expect($.getJSON).toHaveBeenCalled();
                expect(result).toEqual(jasmine.objectContaining({
                    responseJSON: {
                        cart: {},
                        customer: {},
                        messages: {}
                    }
                }));
            });
        });

        describe('"invalidated" method', function () {
            it('Should be defined', function () {
                expect(obj.hasOwnProperty('invalidate')).toBeDefined();
            });

            it('Does not throw before component is initialized', function () {
                expect(function () {
                    obj.invalidate();
                }).not.toThrow();
            });
        });

        describe('"onAjaxComplete" method', function () {
            it('Should not trigger reload if sections is empty', function () {
                var jsonResponse, settings;

                jsonResponse = jasmine.createSpy();
                spyOn(sectionConfig, 'getAffectedSections').and.returnValue([]);
                spyOn(obj, 'reload');
                settings = {
                    type: 'POST',
                    url: 'http://test.local'
                };
                obj.onAjaxComplete(jsonResponse, settings);
                expect(obj.reload).not.toHaveBeenCalled();
            });
        });

        describe('"Magento_Customer/js/customer-data" method', function () {
            it('Should be defined', function () {
                expect(obj.hasOwnProperty('Magento_Customer/js/customer-data')).toBeDefined();
            });
        });
    });
});
