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
                expect(paging.normalize(2)).toBe(2);
                expect(paging.normalize(4)).toBe(4);
            });

            it('out of boundary values', function () {
                expect(paging.normalize(0)).toBe(1);
                expect(paging.normalize(5)).toBe(4);
            });
        });

        describe('countPages method', function () {
            it('correct number of pages', function () {
                paging.countPages();
                expect(paging.pages).toBe(4);
            });

            it('if no records', function () {
                paging.totalRecords = 0;
                paging.countPages();
                expect(paging.pages).toBe(1);
            });
        });

        describe('page manipualations', function () {
            it('setPage method', function () {
                paging.setPage(2);
                expect(paging.current).toBe(2);
            });

            it('next', function () {
                paging.current = 1;
                paging.next();
                expect(paging.current).toBe(2);
            });

            it('next out of boundary', function () {
                paging.current = 4;
                paging.next();
                expect(paging.current).toBe(4);
            });

            it('prev', function () {
                paging.current = 4;
                paging.prev();
                expect(paging.current).toBe(3);
            });

            it('prev out of boundary', function () {
                paging.current = 1;
                paging.prev();
                expect(paging.current).toBe(1);
            });

            it('goFirst', function () {
                paging.goFirst();
                expect(paging.current).toBe(1);
            });

            it('goLast', function () {
                paging.goLast();
                expect(paging.current).toBe(4);
            });

            it('isFirst for 1st page', function () {
                paging.current = 1;
                expect(paging.isFirst()).toBeTruthy();
            });

            it('isFirst for 2nd page', function () {
                paging.current = 2;
                expect(paging.isFirst()).toBeFalsy();
            });

            it('isLast for last page', function () {
                paging.current = 4;
                expect(paging.isLast()).toBeTruthy();
            });

            it('isLast for first page', function () {
                paging.current = 1;
                expect(paging.isLast()).toBeFalsy();
            });
        });

        describe('countPages method', function () {
            it('correct number of pages', function () {
                paging.countPages();
                expect(paging.pages).toBe(4);
            });

            it('if no records', function () {
                paging.totalRecords = 0;
                paging.countPages();
                expect(paging.pages).toBe(1);
            });
        });

        describe('onPagesChange method', function () {
            it('pages amount became less than current', function () {
                paging.current = 4;
                expect(paging.current).toBe(4);
                paging.onPagesChange(2);
                expect(paging.current).toBe(2);
            });
        });

        describe('ititObservable method', function () {
            it('_current will be defined', function () {
                expect(paging._current).toBeDefined();
            });

            it('read from _current', function () {
                paging.current = 2;
                expect(paging._current()).toBe(2);
            });

            it('write into current', function () {
                spyOn(paging, 'normalize').and.callThrough();
                spyOn(paging._current, 'notifySubscribers');
                paging._current(4);
                expect(paging.current).toBe(4);
                expect(paging._current.notifySubscribers).toHaveBeenCalledWith(4);
            });
        });
    });
});
