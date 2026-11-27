/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * ************************************************************************
 */
/*eslint max-nested-callbacks: 0*/
define(['Magento_Bundle/js/components/bundle-dynamic-rows-grid', 'squire'],
    function (BundleDynamicRowsGrid, Squire) {
        'use strict';

        describe('Magento_Bundle/js/components/bundle-dynamic-rows-grid', function () {
            let dynamicRowsGrid;

            describe('test parseProcessingAddChild method', function () {
                beforeEach(function () {
                    dynamicRowsGrid = new BundleDynamicRowsGrid();
                });

                afterEach(function () {
                    dynamicRowsGrid.destroy();
                });

                it('Check the processingAddChild method should call when recordIndex is a valid number', function () {
                    let data = [4], newData = [4];

                    spyOn(dynamicRowsGrid, 'processingAddChild').and.callThrough();

                    dynamicRowsGrid.parseProcessingAddChild(data, newData);

                    expect(dynamicRowsGrid.processingAddChild).toHaveBeenCalled();
                });

                it('Check the processingAddChild method should not call when recordIndex is inValid number',
                    function () {
                        let data = NaN, newData = [2];

                        spyOn(dynamicRowsGrid, 'processingAddChild').and.callThrough();

                        dynamicRowsGrid.parseProcessingAddChild(data, newData);

                        expect(dynamicRowsGrid.processingAddChild).not.toHaveBeenCalled();
                    });
            });
            describe('selection validation during paging', function () {
                var injector, callbacks, resolverMock, field, row, GridConstructor, CollectionConstructor, registry;

                /** Flush pending rendering callbacks. */
                function flush() {
                    while (callbacks.length) {
                        callbacks.shift()();
                    }
                }

                beforeEach(function (done) {
                    injector = new Squire();
                    callbacks = [];
                    resolverMock = function (callback, context) {
                        callbacks.push(callback.bind(context));
                    };
                    injector.mock('rjsResolver', resolverMock);
                    injector.mock('mage/requirejs/resolver', resolverMock);
                    injector.require([
                        'Magento_Bundle/js/components/bundle-dynamic-rows-grid',
                        'uiCollection',
                        'uiRegistry',
                        'knockoutjs/knockout-es5',
                        'Magento_Ui/js/lib/knockout/extender/observable_array'
                    ], function (Grid, Collection, contextRegistry) {
                        GridConstructor = Grid;
                        CollectionConstructor = Collection;
                        registry = contextRegistry;
                        dynamicRowsGrid = new Grid({name: 'bundle_paging_test'});
                        field = {
                            validate: jasmine.createSpy('validate'),
                            focused: jasmine.createSpy('focused'),
                            hasChanged: jasmine.createSpy('hasChanged').and.returnValue(false)
                        };
                        field.validate.and.returnValue({valid: false, target: field});
                        row = new Collection();
                        row.elems([field]);
                        dynamicRowsGrid.elems([row]);
                        dynamicRowsGrid.recordData([{}, {}]);
                        dynamicRowsGrid.pages(3);
                        spyOn(dynamicRowsGrid, 'clear').and.returnValue(dynamicRowsGrid);
                        spyOn(dynamicRowsGrid, 'initChildren').and.returnValue(dynamicRowsGrid);
                        spyOn(dynamicRowsGrid, '_updatePagesQuantity');
                        spyOn(dynamicRowsGrid, '_reducePages');
                        dynamicRowsGrid.onPageSizeChange();
                        flush();
                        dynamicRowsGrid.clear.calls.reset();
                        dynamicRowsGrid.initChildren.calls.reset();
                        field.validate.calls.reset();
                        done();
                    });
                });

                afterEach(function () {
                    row.elems([]);
                    row.destroy();
                    dynamicRowsGrid.elems([]);
                    dynamicRowsGrid.destroy();
                    injector.clean();
                });

                /** Mark the selection as corrected. */
                function allowPaging() {
                    field.validate.and.returnValue({valid: true, target: field});
                }

                it('blocks next and focuses an unchanged invalid field without clearing its row', function () {
                    dynamicRowsGrid.nextPage();

                    expect(dynamicRowsGrid.currentPage()).toBe(1);
                    expect(dynamicRowsGrid.clear).not.toHaveBeenCalled();
                    expect(dynamicRowsGrid.elems()).toEqual([row]);
                    expect(field.validate).toHaveBeenCalled();
                    expect(field.focused).toHaveBeenCalledWith(true);
                });

                it('blocks previous after reaching the second page with valid fields', function () {
                    allowPaging();
                    dynamicRowsGrid.nextPage();
                    dynamicRowsGrid.clear.calls.reset();
                    field.validate.and.returnValue({valid: false, target: field});

                    dynamicRowsGrid.previousPage();

                    expect(dynamicRowsGrid.currentPage()).toBe(2);
                    expect(dynamicRowsGrid.clear).not.toHaveBeenCalled();
                });

                it('restores a typed page number and preserves changed invalid fields', function () {
                    field.hasChanged.and.returnValue(true);
                    dynamicRowsGrid.currentPage('3');

                    expect(dynamicRowsGrid.currentPage()).toBe(1);
                    expect(dynamicRowsGrid.clear).not.toHaveBeenCalled();
                    expect(field.focused).toHaveBeenCalledWith(true);
                });

                it('allows both buttons and typed pages after correction', function () {
                    dynamicRowsGrid.nextPage();
                    allowPaging();
                    dynamicRowsGrid.nextPage();
                    expect(dynamicRowsGrid.currentPage()).toBe(2);
                    dynamicRowsGrid.previousPage();
                    expect(dynamicRowsGrid.currentPage()).toBe(1);
                    dynamicRowsGrid.currentPage('3');
                    expect(dynamicRowsGrid.currentPage()).toBe('3');
                    expect(dynamicRowsGrid.validatedPage).toBe(3);
                    expect(dynamicRowsGrid.initChildren.calls.count()).toBe(3);
                });

                it('restores a numeric page after accepting a typed page number', function () {
                    allowPaging();
                    dynamicRowsGrid.currentPage('2');
                    field.validate.and.returnValue({valid: false, target: field});

                    dynamicRowsGrid.currentPage('3');

                    expect(dynamicRowsGrid.currentPage()).toBe(2);
                    expect(dynamicRowsGrid.validatedPage).toBe(2);
                });

                it('restores page size without reloading invalid fields', function () {
                    var sizeChanged = jasmine.createSpy('sizeChanged');

                    dynamicRowsGrid.on('pageSize', sizeChanged);
                    dynamicRowsGrid.set('pageSize', 30);
                    flush();

                    expect(dynamicRowsGrid.pageSize).toBe(20);
                    expect(sizeChanged).toHaveBeenCalledWith(20, jasmine.anything());
                    expect(dynamicRowsGrid.clear).not.toHaveBeenCalled();
                    expect(field.focused).toHaveBeenCalledWith(true);
                });

                it('reloads and remembers an accepted page size', function () {
                    allowPaging();
                    dynamicRowsGrid.set('pageSize', 30);
                    flush();
                    expect(dynamicRowsGrid.pageSize).toBe(30);
                    expect(dynamicRowsGrid.initChildren).toHaveBeenCalled();
                    dynamicRowsGrid.clear.calls.reset();
                    field.validate.and.returnValue({valid: false, target: field});
                    dynamicRowsGrid.set('pageSize', 50);
                    flush();
                    expect(dynamicRowsGrid.pageSize).toBe(30);
                    expect(dynamicRowsGrid.clear).not.toHaveBeenCalled();
                });

                it('keeps the linked initial size when persisted selections are invalid', function () {
                    var sizes = new CollectionConstructor({name: 'bundle_paging_test_sizes', value: 50});

                    dynamicRowsGrid.elems([]);
                    dynamicRowsGrid.destroy();
                    registry.set(sizes.name, sizes);
                    dynamicRowsGrid = new GridConstructor({
                        name: 'bundle_paging_test',
                        sizesConfig: {name: sizes.name}
                    });
                    dynamicRowsGrid.elems([row]);
                    spyOn(dynamicRowsGrid, 'reload');

                    flush();

                    expect(dynamicRowsGrid.pageSize).toBe(50);
                    expect(dynamicRowsGrid.validatedPageSize).toBe(50);
                    expect(field.validate).not.toHaveBeenCalled();
                    expect(dynamicRowsGrid.reload).toHaveBeenCalled();
                    dynamicRowsGrid.reload.calls.reset();
                    dynamicRowsGrid.set('pageSize', 100);
                    flush();
                    expect(dynamicRowsGrid.pageSize).toBe(50);
                    expect(sizes.value).toBe(50);
                    expect(dynamicRowsGrid.reload).not.toHaveBeenCalled();
                    sizes.destroy();
                });

                it('validates every field and focuses only the first invalid field', function () {
                    var second = {
                        validate: jasmine.createSpy('validate'),
                        focused: jasmine.createSpy('focused')
                    };

                    second.validate.and.returnValue({valid: false, target: second});
                    row.elems([field, second]);

                    expect(dynamicRowsGrid.validateElements()).toBeFalse();
                    expect(second.validate).toHaveBeenCalled();
                    expect(field.focused).toHaveBeenCalledWith(true);
                    expect(second.focused).not.toHaveBeenCalled();
                });

                it('allows initialization when no selections are rendered', function () {
                    dynamicRowsGrid.elems([]);
                    dynamicRowsGrid.set('pageSize', 30);
                    flush();
                    dynamicRowsGrid.currentPage(2);

                    expect(dynamicRowsGrid.pageSize).toBe(30);
                    expect(dynamicRowsGrid.currentPage()).toBe(2);
                    expect(field.validate).not.toHaveBeenCalled();
                });
            });
        });
    });
