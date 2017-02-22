/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'Magento_Ui/js/grid/paging/paging'
], function (Paging) {
    'use strict';

    describe('Magento_Ui/js/grid/paging/paging', function () {
        var paging;

        beforeEach(function () {
            paging = new Paging({
                pageSize: 2
            });
            paging.totalRecords = 7;
        });

        describe('Normalize method', function () {
            it('not a number + empty value', function () {
                expect(paging.normalize(undefined)).toBe(1);
                expect(paging.normalize(true)).toBe(1);
                expect(paging.normalize('a')).toBe(1);
            });

            it('normal + boundary values', function () {
                expect(paging.normalize(1)).toBe(1);
            });

            it('out of boundary values', function () {
                expect(paging.normalize(0)).toBe(1);
            });
        });

        describe('onPagesChange method', function () {
            it('Check call "onPagesChange" method', function () {
                paging.updateCursor = jasmine.createSpy();
                paging.onPagesChange();
                expect(paging.updateCursor).toHaveBeenCalled();
            });
        });

        describe('initObservable method', function () {
            it('_current will be defined', function () {
                expect(paging._current).toBeDefined();
            });

            it('read from _current', function () {
                paging.current = 2;
                expect(paging._current()).toBe(2);
            });
        });
    });
});
