/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
/*jscs:disable requirePaddingNewLinesInObjects*/
/*jscs:disable jsDoc*/

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/components/collection/item'
], function (_, registry, Constr) {
    'use strict';

    describe('Magento_Ui/js/form/components/collection/item', function () {
        var obj = new Constr({
            provider: 'provName',
            name: '',
            index: ''
        });

        describe('"initObservable" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initObservable')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.initObservable;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.initObservable()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.initObservable();

                expect(type).toEqual('object');
            });
            it('Check called "this.observe" method', function () {
                obj.observe = jasmine.createSpy();
                obj.initObservable();
                expect(obj.observe).toHaveBeenCalled();
            });
        });
        describe('"initElement" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initElement')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.initElement;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called with object argument', function () {
                var arg = {
                    initContainer: function () {
                    }
                };

                expect(obj.initElement(arg)).toBeDefined();
            });
            it('Check returned value type if method called with object argument', function () {
                var arg = {
                        initContainer: function () {
                        }
                    },
                    type = typeof obj.initElement(arg);

                expect(type).toEqual('object');
            });
            it('Check called "this.insertToIndexed" method with object argument', function () {
                var arg = {
                    initContainer: function () {
                    }
                };

                obj.insertToIndexed = jasmine.createSpy();
                obj.initElement(arg);
                expect(obj.insertToIndexed).toHaveBeenCalledWith(arg);
            });
        });
        describe('"insertToIndexed" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('insertToIndexed')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.insertToIndexed;

                expect(type).toEqual('function');
            });
            it('Check called "insertToIndexed" method with object argument', function () {
                var arg = {
                    initContainer: function () {
                    }
                };

                obj.insertToIndexed(arg);
                expect(obj.insertToIndexed).toHaveBeenCalledWith(arg);
            });
        });
        describe('"formatPreviews" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('formatPreviews')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.formatPreviews;

                expect(type).toEqual('function');
            });
            it('Check returned value if call method with array arguments', function () {
                expect(obj.formatPreviews(['1', '2', '3'])).toEqual(
                    [
                        {
                            'items': ['1'],
                            'separator': ' ',
                            'prefix': ''
                        },
                        {
                            'items': ['2'],
                            'separator': ' ',
                            'prefix': ''
                        },
                        {
                            'items': ['3'],
                            'separator': ' ',
                            'prefix': ''
                        }
                    ]
                );
            });
        });
        describe('"buildPreview" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('buildPreview')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.buildPreview;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called with object argument', function () {
                var arg = {
                    items: [],
                    prefix: 'magento'
                };

                obj.getPreview = jasmine.createSpy().and.callFake(function () {
                    return [];
                });

                expect(obj.buildPreview(arg)).toBeDefined();
            });
            it('Check returned value type if method called with object argument', function () {
                var arg = {
                        items: [],
                        prefix: 'magento'
                    };

                obj.getPreview = jasmine.createSpy().and.callFake(function () {
                    return [];
                });

                expect(typeof obj.buildPreview(arg)).toEqual('string');
            });
            it('Check called "this.getPreview" method with object argument', function () {
                var arg = {
                    items: [],
                    prefix: 'magento'
                };

                obj.getPreview = jasmine.createSpy().and.callFake(function () {
                    return [];
                });
                obj.buildPreview(arg);
                expect(obj.getPreview).toHaveBeenCalledWith(arg.items);
            });
        });
        describe('"hasPreview" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('hasPreview')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.hasPreview;

                expect(type).toEqual('function');
            });
            it('Check returned value type if method called with object argument', function () {
                var arg = {
                        items: [],
                        prefix: 'magento'
                    },
                    type = typeof obj.hasPreview(arg);

                expect(type).toEqual('boolean');
            });
            it('Check called "this.getPreview" method with object argument', function () {
                var arg = {
                    items: [],
                    prefix: 'magento'
                };

                obj.getPreview = jasmine.createSpy().and.callFake(function () {
                    return [];
                });
                obj.hasPreview(arg);
                expect(obj.getPreview).toHaveBeenCalledWith(arg.items);
            });
        });
        describe('"getPreview" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('getPreview')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.getPreview;

                expect(type).toEqual('function');
            });
            it('Check returned value type if method called with object argument', function () {
                var arg = {
                        items: [],
                        prefix: 'magento'
                    },
                    type = typeof obj.getPreview(arg);

                expect(type).toEqual('object');
            });
        });
    });
});
