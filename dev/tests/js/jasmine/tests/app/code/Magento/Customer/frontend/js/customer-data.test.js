/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global _ */
/* eslint max-nested-callbacks: 0 */
define([
    'squire',
    'jquery',
    'jquery/jquery-storageapi'
], function (Squire, $) {
    'use strict';

    var injector = new Squire(),
        sectionConfig,
        originalGetJSON,
        originalReload,
        originalInitNamespaceStorage,
        originalEach,
        obj;

    describe('Magento_Customer/js/customer-data', function () {

        beforeEach(function (done) {
            injector.require([
                'Magento_Customer/js/customer-data',
                'Magento_Customer/js/section-config'
            ], function (Constr, sectionConfiguration) {
                obj = Constr;
                sectionConfig = sectionConfiguration;
                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {
            }
        });

        describe('"init" method', function () {
            var storageInvalidation = {
                    /**
                     * Mock Keys Method
                     * @returns array
                     */
                    keys: function () {
                        return ['section'];
                    }
                },
                dataProvider = {
                    /**
                     * Mock getFromStorage Method
                     * @returns array
                     */
                    getFromStorage: function () {
                        return ['section'];
                    }
                },
                storage = {
                    /**
                     * Mock Keys Method
                     * @returns array
                     */
                    keys: function () {
                        return ['section'];
                    }
                };

            beforeEach(function () {
                originalReload = obj.reload;
                originalInitNamespaceStorage = $.initNamespaceStorage;
                spyOn(obj, 'reload').and.returnValue(true);
                spyOn($, 'initNamespaceStorage').and.callFake(function (name) {
                    var ns = {
                        localStorage: {
                            cookie: false,
                            _ns: name
                        },
                        sessionStorage: {
                            cookie: false,
                            _ns: name
                        }
                    };

                    $.namespaceStorages[name] = ns;

                    return ns;
                });
                spyOn(dataProvider, 'getFromStorage');
                spyOn(storage, 'keys').and.returnValue(['section']);
                spyOn(storageInvalidation, 'keys').and.returnValue(['section']);
            });

            afterEach(function () {
                obj.reload = originalReload;
                $.initNameSpaceStorage = originalInitNamespaceStorage;
                $.namespaceStorages = {};
            });

            it('Should be defined', function () {
                expect(obj.hasOwnProperty('init')).toBeDefined();
            });

            it('Does not throw before component is initialized', function () {
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
                obj.init();
                expect(obj.reload).toHaveBeenCalled();
            });

            it('Calls "reload" method when expired sections do not exist', function () {
                spyOn(obj, 'getExpiredSectionNames').and.returnValue([]);

                _.isEmpty = jasmine.createSpy().and.returnValue(false);

                obj.init();
                expect(obj.reload).toHaveBeenCalled();
            });
        });

        describe('"getExpiredSectionNames" method', function () {
            it('Should be defined', function () {
                expect(obj.hasOwnProperty('getExpiredSectionNames')).toBeDefined();
            });

            it('Does not throw before component is initialized', function () {
                expect(function () {
                    obj.getExpiredSectionNames();
                }).not.toThrow();
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
                originalGetJSON = jQuery.getJSON;
                jQuery.getJSON = jasmine.createSpy().and.callFake(function () {
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
                jQuery.getJSON = originalGetJSON;
            });

            it('Should be defined', function () {
                expect(obj.hasOwnProperty('reload')).toBeDefined();
            });

            it('Does not throw before component is initialized', function () {
                expect(function () {
                    obj.reload();
                }).not.toThrow();
            });

            it('Returns proper sections object when passed array with a single section name', function () {
                var result;

                spyOn(sectionConfig, 'filterClientSideSections').and.returnValue(['section']);

                jQuery.getJSON = jasmine.createSpy().and.callFake(function (url, parameters) {
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

            it('Returns proper sections object when passed array with a multiple section names', function () {
                var result;

                spyOn(sectionConfig, 'filterClientSideSections').and.returnValue(['cart,customer,messages']);

                jQuery.getJSON = jasmine.createSpy().and.callFake(function (url, parameters) {
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

            it('Returns all sections when passed wildcard string', function () {
                var result;

                jQuery.getJSON = jasmine.createSpy().and.callFake(function (url, parameters) {
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

                expect(jQuery.getJSON).toHaveBeenCalled();
                expect(result).toEqual(jasmine.objectContaining({
                    responseJSON: {
                        cart: {},
                        customer: {},
                        messages: {}
                    }
                }));
            });
        });

        describe('"invalidate" method', function () {
            it('Should be defined', function () {
                expect(obj.hasOwnProperty('invalidate')).toBeDefined();
            });

            it('Does not throw before component is initialized', function () {
                expect(function () {
                    obj.invalidate();
                }).not.toThrow();
            });
        });

        describe('"Magento_Customer/js/customer-data" method', function () {
            it('Should be defined', function () {
                expect(obj.hasOwnProperty('Magento_Customer/js/customer-data')).toBeDefined();
            });
        });
    });
});
