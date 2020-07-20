/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint max-nested-callbacks: 0 */

define([
    'squire'
], function (Squire) {
    'use strict';

    var injector = new Squire(),
        originalGetJSON,
        storage,
        storageInvalidation = {},
        obj;

    beforeEach(function (done) {
        injector.require(['Magento_Customer/js/customer-data'], function (Constr) {
            originalGetJSON = $.getJSON;
            obj = Constr;
            done();
        });
    });

    afterEach(function () {
        try {
            injector.clean();
            injector.remove();
            $.getJSON = originalGetJSON;
        } catch (e) {
        }
    });

    describe('Magento_Customer/js/customer-data', function () {

        describe('"init" method', function () {
            beforeEach(function () {
                spyOn(obj, "reload").and.returnValue(true);

                storageInvalidation = {
                    keys: function () {
                        return ['section'];
                    }
                }

                var dataProvider = {
                    getFromStorage: function (sections) {
                        return ['section'];
                    }
                };

                storage = {
                    keys: function () {
                        return ['section'];
                    }
                };

                spyOn(dataProvider, "getFromStorage");
                spyOn(storage, "keys").and.returnValue(['section']);
                spyOn(storageInvalidation, "keys").and.returnValue(['section']);
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
                spyOn(obj, "getExpiredSectionNames").and.returnValue([]);
                obj.init();
                expect(obj.getExpiredSectionNames).toHaveBeenCalled();
            });

            it('Calls "reload" method when expired sections exist', function () {
                spyOn(obj, "getExpiredSectionNames").and.returnValue(['section']);
                obj.init();
                expect(obj.reload).toHaveBeenCalled();
            });

            it('Calls "reload" method when expired sections do not exist', function () {
                spyOn(obj, "getExpiredSectionNames").and.returnValue([]);

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
            it('Should be defined', function () {
                expect(obj.hasOwnProperty('set')).toBeDefined();
            });

            it('Does not throw before component is initialized', function () {
                expect(function () {
                    obj.set('cart', {});
                }).not.toThrow();
            });
        });

        describe('"reload" method', function () {
            beforeEach(function () {
                $.getJSON = jasmine.createSpy().and.callFake(function (url, parameters) {
                    var deferred = $.Deferred();

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

                $.getJSON = jasmine.createSpy().and.callFake(function (url, parameters) {
                    var deferred = $.Deferred();

                    deferred.promise().done = function () {
                        return {
                            responseJSON: {
                                section: {}
                            }
                        };
                    };

                    expect(parameters).toEqual(jasmine.objectContaining({
                        "sections": "section"
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

                $.getJSON = jasmine.createSpy().and.callFake(function (url, parameters) {
                    var deferred = $.Deferred();

                    expect(parameters).toEqual(jasmine.objectContaining({
                        "sections": "cart,customer,messages"
                    }));

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

                $.getJSON = jasmine.createSpy().and.callFake(function (url, parameters) {
                    var deferred = $.Deferred();

                    expect(parameters).toEqual(jasmine.objectContaining({
                        "force_new_section_timestamp": true
                    }));

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

        describe('"invalidate" method', function () {
            it('Should be defined', function () {
                expect(obj.hasOwnProperty('invalidate')).toBeDefined();
            });
        });

        describe('"Magento_Customer/js/customer-data" method', function () {
            it('Should be defined', function () {
                expect(obj.hasOwnProperty('Magento_Customer/js/customer-data')).toBeDefined();
            });
        });
    });
});
