/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'underscore',
    'Magento_Ui/js/dynamic-rows/dynamic-rows',
    'uiRegistry',
    'uiEvents'
], function (_, DynamicRows, registry, events) {
    'use strict';

    var model,

        /**
         * @param {Number} index
         * @returns {Object}
         * @constructor
         */
        ElementMock = function (index) {
            return {
                /**
                 * @return void
                 */
                destroy: function () {},
                index: index
            };
        };

    /**
     * Run before each test method
     *
     * @return void
     */
    beforeEach(function () {
        model = new DynamicRows({});
    });

    /**
     * Testing changePage and delete record methods
     *
     * @return void
     */
    describe('Magento_Ui/js/dynamic-rows/dynamic-rows', function () {
        it('Calls "setMaxPosition" method without position.', function () {
            model.maxPosition = 0;
            model.setMaxPosition(false);

            expect(model.maxPosition).toEqual(1);
        });

        it('Calls "setMaxPosition" method with position 0.', function () {
            var elem = {};

            model.checkMaxPosition = jasmine.createSpy();
            model.sort = jasmine.createSpy();
            model.setMaxPosition(0, elem);

            expect(model.checkMaxPosition).toHaveBeenCalledWith(0);
            expect(model.sort).toHaveBeenCalledWith(0, elem);
        });

        it('changePage without Records', function () {
            /**
             * Mock function which return length of record data
             *
             * @returns {Object}
             */
            model.recordData = function () {
                return {
                    length: 0
                };
            };

            expect(model.changePage(1)).toBeFalsy();
        });

        it('changePage with Fake Page', function () {
            /**
             * Mock function, which return the number of pages
             *
             * @returns {Number}
             */
            model.pages = function () {
                return 3;
            };

            expect(model.changePage(4)).toBeFalsy();
        });

        it('changePage', function () {
            model.startIndex = 0;
            model.pageSize = 3;
            model.relatedData = [
                {
                    'a': 'b'
                },
                {
                    'b': 'c'
                },
                {
                    'v': 'g'
                }
            ];

            /**
             * @returns {Number}
             */
            model.pages = function () {
                return 3;
            };
            model.changePage(2);

            expect(model.templates.record.recordId).toBe(2);//last record number is 3
        });

        it('deleteRecord with Delete Property', function () {
            var elems,
                recordInstanceMock = new ElementMock(1),
                elem2 = new ElementMock(2);

            spyOn(recordInstanceMock, 'destroy');
            model.recordData({
                1: {}
            });
            elems = [
                recordInstanceMock,
                elem2
            ];
            model.elems(elems);
            model.deleteProperty = true;
            model.deleteRecord(1, 1);
            expect(model.recordData()).toEqual([]);
        });

        it('"initHeader" sortOrder', function () {
            var labels = [{
                    name: 'Name 1',
                    config: {
                        label: 'Label 1',
                        validation: false,
                        columnsHeaderClasses: '',
                        sortOrder: 10
                    }
                }, {
                    name: 'Name 2',
                    config: {
                        label: 'Label 2',
                        validation: false,
                        columnsHeaderClasses: '',
                        sortOrder: 5
                    }
                }],
                result = [{
                    defaultLabelVisible: true,
                    label: 'Label 2',
                    name: 'Name 2',
                    required: false,
                    columnsHeaderClasses: '',
                    sortOrder: 5
                }, {
                    defaultLabelVisible: true,
                    label: 'Label 1',
                    name: 'Name 1',
                    required: false,
                    columnsHeaderClasses: '',
                    sortOrder: 10
                }];

            model.childTemplate = {
                children: labels
            };
            expect(JSON.stringify(model.labels())).toEqual(JSON.stringify(result));
        });

        it('Check _updatePagesQuantity method call.', function () {
            model._updatePagesQuantity = jasmine.createSpy();

            model.reload();

            expect(model._updatePagesQuantity).toHaveBeenCalled();
        });

        it('Check number of pages is updated after reloading dynamic-rows.', function () {
            model.pageSize = 1;
            model.relatedData = [
                {
                    name: 'first'
                },
                {
                    name: 'second'
                },
                {
                    name: 'third'
                }
            ];

            model.reload();
            expect(model.pages()).toEqual(3);

            model.currentPage(3);
            model.pageSize = 2;

            model.reload();
            expect(model.pages()).toEqual(2);
            expect(model.currentPage()).toEqual(2);
        });

        it('should process pages before addChild', function () {
            var ctx = {},
                index = 5,
                prop = 'someProp';

            model.pageSize = 2;
            model.relatedData = [
                {
                    name: 'first'
                },
                {
                    name: 'second'
                },
                {
                    name: 'third'
                },
                {
                    name: 'fourth'
                },
                {
                    name: 'fifth'
                }
            ];
            model.bubble = jasmine.createSpy();
            model.addChild = jasmine.createSpy();
            model.processingAddChild(ctx, index, prop);
            expect(model.bubble).toHaveBeenCalledWith('addChild', false);
            expect(model.pages()).toEqual(3);
            expect(model.currentPage()).toEqual(3);
            expect(model.addChild).toHaveBeenCalledWith(ctx, index, prop);
        });

        describe('validateOffPageRecords', function () {
            beforeEach(function () {
                jasmine.clock().install();
                model.name = 'rows';
                model.source = {
                    set: jasmine.createSpy()
                };
                model.initChildren = jasmine.createSpy();
                model.delegate = jasmine.createSpy().and.returnValue([]);
                model.pageSize = 2;
                model.childTemplate = {
                    children: {
                        price: {
                            name: 'price',
                            config: {
                                dataScope: 'price',
                                validation: {
                                    'required-entry': true
                                }
                            }
                        },
                        group: {
                            name: 'group',
                            config: {
                                dataScope: ''
                            },
                            children: {
                                qty: {
                                    name: 'qty',
                                    config: {
                                        dataScope: 'qty',
                                        validation: {
                                            'required-entry': true
                                        }
                                    }
                                }
                            }
                        },
                        percent: {
                            name: 'percent',
                            config: {
                                dataScope: 'percent',
                                visible: false,
                                validation: {
                                    'required-entry': true
                                }
                            }
                        },
                        sku: {
                            name: 'sku',
                            config: {
                                dataScope: 'sku',
                                imports: {
                                    visible: '${ $.provider }:${ $.parentScope }.canEdit'
                                },
                                validation: {
                                    'required-entry': true
                                }
                            }
                        }
                    }
                };
                model.pages(3);
                model.currentPage(1);
                spyOn(registry, 'get');
            });

            afterEach(function () {
                jasmine.clock().uninstall();
            });

            it('marks the form invalid and opens the first page with an invalid record', function () {
                model.relatedData = [
                    {price: '1', qty: '1'},
                    {price: '2', qty: '1'},
                    {price: '3', qty: '1'},
                    {price: '', qty: '1'},
                    {price: '', qty: ''}
                ];

                model.validateOffPageRecords();

                expect(model.source.set).toHaveBeenCalledWith('params.invalid', true);
                expect(model.currentPage()).toEqual(1);

                jasmine.clock().tick(1);

                expect(model.currentPage()).toEqual(2);
                expect(registry.get).toHaveBeenCalledWith('rows.2.price', jasmine.any(Function));
                expect(registry.get).toHaveBeenCalledWith('rows.3.price', jasmine.any(Function));
                expect(registry.get).toHaveBeenCalledWith('rows.3.group.qty', jasmine.any(Function));
                expect(registry.get).not.toHaveBeenCalledWith('rows.4.price', jasmine.any(Function));
                expect(registry.get).not.toHaveBeenCalledWith('rows.3.percent', jasmine.any(Function));
                expect(registry.get).not.toHaveBeenCalledWith('rows.3.sku', jasmine.any(Function));
            });

            it('validates fields nested in containers', function () {
                model.relatedData = [
                    {price: '1', qty: '1'},
                    {price: '2', qty: '1'},
                    {price: '3', qty: '1'},
                    {price: '4', qty: '1'},
                    {price: '5', qty: ''}
                ];

                model.validateOffPageRecords();
                jasmine.clock().tick(1);

                expect(model.source.set).toHaveBeenCalledWith('params.invalid', true);
                expect(model.currentPage()).toEqual(3);
            });

            it('ignores fields hidden by configuration or bound to another component', function () {
                model.relatedData = [
                    {price: '1', qty: '1'},
                    {price: '2', qty: '1', percent: '', sku: ''},
                    {price: '3', qty: '1', percent: '', sku: ''}
                ];

                model.validateOffPageRecords();
                jasmine.clock().tick(1);

                expect(model.source.set).not.toHaveBeenCalled();
                expect(model.currentPage()).toEqual(1);
            });

            it('keeps the form valid when the records of all pages are valid', function () {
                model.relatedData = [
                    {price: '', qty: ''},
                    {price: '2', qty: '1'},
                    {price: '3', qty: '1'}
                ];

                model.validateOffPageRecords();
                jasmine.clock().tick(1);

                expect(model.source.set).not.toHaveBeenCalled();
                expect(model.currentPage()).toEqual(1);
            });

            it('stays on the current page when it shows validation errors itself', function () {
                model.relatedData = [
                    {price: '', qty: '1'},
                    {price: '2', qty: '1'},
                    {price: '', qty: '1'}
                ];
                model.delegate.and.returnValue([{}]);

                model.validateOffPageRecords();
                jasmine.clock().tick(1);

                expect(model.source.set).toHaveBeenCalledWith('params.invalid', true);
                expect(model.delegate).toHaveBeenCalledWith('checkInvalid');
                expect(model.currentPage()).toEqual(1);
                expect(registry.get).not.toHaveBeenCalled();
            });

            it('runs on the validation of the form', function () {
                var provider = _.extend({
                    name: 'dynamicRowsTestProvider',
                    get: function () {},
                    set: jasmine.createSpy()
                }, events);

                registry.get.and.callThrough();
                registry.set(provider.name, provider);
                model = new DynamicRows({
                    name: 'rows',
                    provider: provider.name,
                    pageSize: 2,
                    childTemplate: {
                        children: {
                            price: {
                                name: 'price',
                                config: {
                                    dataScope: 'price',
                                    validation: {
                                        'required-entry': true
                                    }
                                }
                            }
                        }
                    }
                });
                model.relatedData = [{price: '1'}, {price: '2'}, {price: ''}];
                model.pages(2);

                provider.trigger('data.validate');
                registry.remove(provider.name);

                expect(provider.set).toHaveBeenCalledWith('params.invalid', true);
            });

            it('skips the check when all records fit on one page', function () {
                model.relatedData = [
                    {price: '', qty: ''}
                ];
                model.pages(1);

                model.validateOffPageRecords();
                jasmine.clock().tick(1);

                expect(model.source.set).not.toHaveBeenCalled();
            });
        });
    });
});
