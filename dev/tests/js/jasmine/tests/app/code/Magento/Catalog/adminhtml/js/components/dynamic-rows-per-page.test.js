/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_Catalog/js/components/dynamic-rows-per-page', function () {
        var injector,
            model,
            mocks = {
                rjsResolver: function (callback, context) {
                    callback.call(context);
                }
            };

        beforeEach(function (done) {
            injector = new Squire();
            injector.mock(mocks);
            injector.require(['Magento_Catalog/js/components/dynamic-rows-per-page'], function (DynamicRows) {
                model = new DynamicRows({
                    index: 'dynamic_rows',
                    name: 'dynamic_rows',
                    indexField: 'id',
                    dataScope: '',
                    rows: [{
                        identifier: 'row'
                    }]
                });
                model.reload = jasmine.createSpy();
                model.elems = jasmine.createSpy().and.returnValue([{}]);
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

        describe('"onPageSizeChange" method', function () {
            it('does not reload on initial page size synchronization', function () {
                model.pageSize = 20;
                model.onPageSizeChange();

                expect(model.currentPageSize).toBe(20);
                expect(model.reload).not.toHaveBeenCalled();
            });

            it('does not reload when page size changes type but not value', function () {
                model.pageSize = 20;
                model.onPageSizeChange();
                model.pageSize = '20';
                model.onPageSizeChange();

                expect(model.currentPageSize).toBe(20);
                expect(model.reload).not.toHaveBeenCalled();
            });

            it('reloads when page size changes value and rows exist', function () {
                model.pageSize = 20;
                model.onPageSizeChange();
                model.pageSize = '30';
                model.onPageSizeChange();

                expect(model.currentPageSize).toBe(30);
                expect(model.reload).toHaveBeenCalled();
            });
        });
    });
});
