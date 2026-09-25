/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * ************************************************************************
 */
define(['jquery', 'mage/adminhtml/tools', 'mage/adminhtml/grid'], function ($) {
    'use strict';
    describe('mage/adminhtml/grid', function () {
        let gridInstance, originalVarienGridInitializeFn, originalFormKey;

        beforeEach(function () {
            originalVarienGridInitializeFn = window.varienGrid.prototype.initialize;
            originalFormKey = window.FORM_KEY;
            window.FORM_KEY = 'test-form-key';
            window.varienGrid.prototype.initialize = jasmine.createSpy('initialize');
            gridInstance = new window.varienGrid();
            gridInstance.filterVar = 'filter';
            gridInstance.pageVar = 'page';
            gridInstance.sortVar = 'sort';
            gridInstance.dirVar = 'dir';
            spyOn(gridInstance, 'reload');
        });

        afterEach(function () {
            window.varienGrid.prototype.initialize = originalVarienGridInitializeFn;
            window.FORM_KEY = originalFormKey;
        });

        it('clears stale filter path and query values while preserving other URL data', function () {
            let callback = jasmine.createSpy('callback'),
                expectedUrl = 'https://test.com/admin/grid/?keep=1&filter=#summary';

            gridInstance.url = 'https://test.com/admin/grid/filter/old/filter/new/?filter=old&keep=1#summary';

            gridInstance.resetFilter(callback);

            expect(gridInstance.url).toBe(expectedUrl);
            expect(gridInstance.reload).toHaveBeenCalledWith(expectedUrl, callback);
            expect(gridInstance.reload.calls.count()).toBe(1);
        });

        it('replaces stale filter values when applying a non-empty filter', function () {
            let callback = jasmine.createSpy('callback'),
                expectedUrl = 'https://test.com/admin/grid/filter/encoded/?keep=1#summary';

            gridInstance.url = 'https://test.com/admin/grid/filter/old/?filter=stale&keep=1#summary';
            spyOn(window, '$$').and.returnValue([{value: 'active'}]);
            spyOn(Form, 'serializeElements').and.returnValue('status=active');
            spyOn(window.Base64, 'encode').and.returnValue('encoded');

            gridInstance.doFilter(callback);

            expect(gridInstance.url).toBe(expectedUrl);
            expect(gridInstance.reload).toHaveBeenCalledWith(expectedUrl, callback);
        });

        it('handles parameter names that contain regular expression characters', function () {
            let expectedUrl = 'https://test.com/admin/grid/?keep=1&filter%5Bstatus%5D=#summary';

            gridInstance.url =
                'https://test.com/admin/grid/filter[status]/old/?filter%5Bstatus%5D=stale&keep=1#summary';

            expect(gridInstance.addVarToUrl('filter[status]', '')).toBe(expectedUrl);
        });

        it('replaces stale sort and direction values without losing other parameters', function () {
            let column = {
                    readAttribute: function (name) {
                        return {'data-sort': 'name', 'data-direction': 'asc'}[name];
                    }
                },
                event = {
                    preventDefault: jasmine.createSpy('preventDefault'),
                    stopPropagation: jasmine.createSpy('stopPropagation')
                },
                expectedUrl = 'https://test.com/admin/grid/sort/name/dir/asc/?keep=1#summary';

            gridInstance.url = 'https://test.com/admin/grid/sort/sku/dir/desc/?sort=old&dir=old&keep=1#summary';
            spyOn(Event, 'findElement').and.returnValue(column);

            gridInstance.doSort(event);

            expect(gridInstance.url).toBe(expectedUrl);
            expect(gridInstance.reload).toHaveBeenCalledWith(expectedUrl);
            expect(event.preventDefault).toHaveBeenCalled();
            expect(event.stopPropagation).toHaveBeenCalled();
        });

        it('replaces a stale page value and preserves an unrelated query parameter', function () {
            let expectedUrl = 'https://test.com/admin/grid/page/3/?keep=1#summary';

            gridInstance.url = 'https://test.com/admin/grid/page/2/?page=1&keep=1#summary';
            gridInstance.setPage(3);

            expect(gridInstance.url).toBe(expectedUrl);
            expect(gridInstance.reload).toHaveBeenCalledWith(expectedUrl);
        });

        it('adds the AJAX flag before a URL fragment', function () {
            let request = {},
                ajax;

            gridInstance.containerId = 'test-grid';
            gridInstance.useAjax = true;
            gridInstance.reload.and.callThrough();
            spyOn($, 'ajax').and.returnValue(request);

            expect(gridInstance.reload('https://test.com/admin/grid/?keep=1#summary')).toBe(request);
            ajax = $.ajax.calls.mostRecent().args[0];
            expect(ajax.url).toBe('https://test.com/admin/grid/?keep=1&ajax=true#summary');
            expect(ajax.data.form_key).toBe('test-form-key');
        });
    });
});
