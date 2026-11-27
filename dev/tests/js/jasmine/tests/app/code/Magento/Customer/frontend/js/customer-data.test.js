/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
                // eslint-disable-next-line no-unused-vars
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

            describe('when the section request fails', function () {
                /**
                 * @param {Object} jqXHR
                 * @param {String} textStatus
                 */
                function failRequestWith(jqXHR, textStatus) {
                    $.getJSON = jasmine.createSpy().and.callFake(function () {
                        return $.Deferred().reject(jqXHR, textStatus).promise();
                    });
                }

                it('Reports the HTTP status and the server message of a JSON error response', function () {
                    failRequestWith({
                        status: 400,
                        statusText: 'Bad Request',
                        responseText: '{"message":"Invalid section"}',
                        responseJSON: {
                            message: 'Invalid section'
                        }
                    }, 'error');

                    expect(function () {
                        obj.reload(['cart'], true);
                    }).toThrowError(Error, /400 Bad Request.*Invalid section/);
                });

                it('Reports the HTTP status of a response that is not JSON without its body', function () {
                    failRequestWith({
                        status: 200,
                        statusText: 'OK',
                        responseText: '<html><body>Maintenance</body></html>'
                    }, 'parsererror');

                    expect(function () {
                        obj.reload(['cart'], true);
                    }).toThrowMatching(function (error) {
                        return /200 OK/.test(error.message) &&
                            error.message.indexOf('<') === -1 &&
                            error.message.indexOf('Maintenance') === -1 &&
                            error.message.indexOf('[object Object]') === -1;
                    });
                });

                it('Reports the HTTP status of an HTML error page without its body', function () {
                    failRequestWith({
                        status: 503,
                        statusText: 'Service Unavailable',
                        responseText: '<html><body>Down for maintenance</body></html>'
                    }, 'error');

                    expect(function () {
                        obj.reload(['cart'], true);
                    }).toThrowMatching(function (error) {
                        return /503 Service Unavailable/.test(error.message) &&
                            error.message.indexOf('<') === -1 &&
                            error.message.indexOf('maintenance') === -1;
                    });
                });

                it('Does not pad the message when the status text is empty', function () {
                    failRequestWith({
                        status: 400,
                        statusText: '',
                        responseJSON: {
                            message: 'X'
                        }
                    }, 'error');

                    expect(function () {
                        obj.reload(['cart'], true);
                    }).toThrowMatching(function (error) {
                        return /400: X$/.test(error.message) &&
                            error.message.indexOf('  ') === -1 &&
                            error.message.indexOf(' :') === -1;
                    });
                });

                it('Does not throw when the request was cancelled', function () {
                    failRequestWith({
                        status: 0,
                        statusText: 'abort'
                    }, 'abort');

                    expect(function () {
                        obj.reload(['cart'], true);
                    }).not.toThrow();
                });
            });
        });

        describe('"disposableCustomerData" extender', function () {
            var knockout;

            beforeEach(function (done) {
                clearLocalStorage();

                injector.require([
                    'ko',
                    'Magento_Customer/js/customer-data'
                ], function (ko, Constr) {
                    knockout = ko;
                    obj = Constr;
                    obj.initStorage();
                    done();
                });
            });

            afterEach(function () {
                jasmine.clock().uninstall();
            });

            it('Keeps the section when its data_id changed after the cleanup was scheduled', function () {
                var observable,
                    result;

                jasmine.clock().install();
                $.cookieStorage.set('section_data_ids', {
                    messages: 100,
                    cart: 5
                });

                observable = knockout.observable({}).extend({
                    disposableCustomerData: 'messages'
                });

                // triggers the subscriber, scheduling the 3s cleanup with data_id = 100
                observable({
                    'data_id': 100
                });

                // fresh messages arrive before the timeout fires, bumping the data_id
                $.cookieStorage.set('section_data_ids', {
                    messages: 200,
                    cart: 5
                });

                jasmine.clock().tick(3000);

                result = $.cookieStorage.get('section_data_ids') || {};
                expect(result.hasOwnProperty('messages')).toBe(true);
            });

            it('Drops the section when its data_id is unchanged when the cleanup fires', function () {
                var observable,
                    result;

                jasmine.clock().install();
                $.cookieStorage.set('section_data_ids', {
                    messages: 100,
                    cart: 5
                });

                observable = knockout.observable({}).extend({
                    disposableCustomerData: 'messages'
                });

                observable({
                    'data_id': 100
                });

                jasmine.clock().tick(3000);

                result = $.cookieStorage.get('section_data_ids') || {};
                expect(result.hasOwnProperty('messages')).toBe(false);
                expect(result.hasOwnProperty('cart')).toBe(true);
            });
        });

        describe('Storage namespace recovery', function () {
            var storageJQuery,
                storageGetJSON;

            beforeEach(function () {
                storageJQuery = $;
                $ = jQuery;
                storageGetJSON = $.getJSON;
                obj.initStorage();
                setupLocalStorage({
                    customer: {data_id: 100, content: {}},
                    cart: {data_id: 200, content: {}}
                });
            });

            afterEach(function () {
                $.getJSON = storageGetJSON;
                $ = storageJQuery;
            });

            [null, 'null'].forEach(function (namespaceValue) {
                var state = namespaceValue === null ? 'missing' : 'JSON-null';

                function resetNamespace(name) {
                    if (namespaceValue === null) {
                        window.localStorage.removeItem(name);
                    } else {
                        window.localStorage.setItem(name, namespaceValue);
                    }
                }

                it('Completes invalidation with a ' + state + ' cache namespace', function () {
                    resetNamespace('mage-cache-storage');

                    obj.invalidate(['customer', 'cart']);

                    expect($.localStorage.get('mage-cache-storage')).toEqual({});
                    expect($.localStorage.get('mage-cache-storage-section-invalidation')).toEqual({
                        customer: true,
                        cart: true
                    });
                    expect($.cookieStorage.get('section_data_ids')).toEqual({
                        customer: 1100,
                        cart: 1200
                    });
                });

                it('Completes updates with ' + state + ' cache and invalidation namespaces', function () {
                    var customer = {data_id: 300, fullname: 'Test Customer'},
                        cart = {data_id: 400, summary_count: 1};

                    resetNamespace('mage-cache-storage');
                    resetNamespace('mage-cache-storage-section-invalidation');
                    $.getJSON = jasmine.createSpy().and.returnValue($.Deferred().resolve({
                        customer: customer,
                        cart: cart
                    }).promise());

                    obj.reload(['customer', 'cart']);

                    expect($.localStorage.get('mage-cache-storage')).toEqual({
                        customer: customer,
                        cart: cart
                    });
                    expect($.localStorage.get('mage-cache-storage-section-invalidation')).toEqual({});
                    expect(obj.get('customer')()).toEqual(customer);
                    expect(obj.get('cart')()).toEqual(cart);
                    expect($.cookieStorage.get('section_data_ids')).toEqual({
                        customer: 300,
                        cart: 400
                    });
                });

                it('Allows array removal from a ' + state + ' namespace', function () {
                    var storage = $.initNamespaceStorage('mage-cache-storage').localStorage;

                    resetNamespace('mage-cache-storage');

                    expect(storage.remove(['customer', 'cart'])).toBe(true);
                    expect(storage.get()).toEqual({});
                });
            });

            it('Preserves unrelated sections when removing from an existing namespace', function () {
                obj.invalidate(['customer']);

                expect($.localStorage.get('mage-cache-storage')).toEqual({
                    cart: {data_id: 200, content: {}}
                });
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

        describe('Customer share scope handling', function () {
            var originalCookieStorage,
                originalLocalStorage;

            beforeEach(function () {
                originalCookieStorage = $.cookieStorage;
                originalLocalStorage = $.localStorage;

                $.cookieStorage = jasmine.createSpyObj('cookieStorage', ['isSet', 'get', 'set', 'setConf']);
                $.localStorage = jasmine.createSpyObj('localStorage', ['isSet', 'get', 'set']);
            });

            afterEach(function () {
                $.cookieStorage = originalCookieStorage;
                $.localStorage = originalLocalStorage;
            });

            it('Should use cookieStorage for login state when customerShare is global (0)', function () {
                init({
                    customerShare: '0',
                    isLoggedIn: '1'
                });

                expect($.cookieStorage.isSet).toHaveBeenCalledWith('mage-customer-login');
                expect($.cookieStorage.get).toHaveBeenCalledWith('mage-customer-login');
            });

            it('Should use localStorage for login state when customerShare is not global (1)', function () {
                init({
                    customerShare: '1',
                    isLoggedIn: '1'
                });

                expect($.localStorage.isSet).toHaveBeenCalledWith('mage-customer-login');
                expect($.localStorage.get).toHaveBeenCalledWith('mage-customer-login');
            });
        });
    });
});
