/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'Magento_Ui/js/grid/columns/multiselect'
], function (_, Multiselect) {
    'use strict';

    describe('ui/js/grid/columns/multiselect', function () {
        var multiSelect;

        beforeEach(function () {
            multiSelect = new Multiselect({
                rows: [],
                index: 'index',
                name: 'name',
                indexField: 'id',
                dataScope: 'scope',
                provider: 'provider'
            });
            multiSelect.source = {
                set: function () {
                }
            };
            spyOn(multiSelect.source, 'set');
        });

        afterEach(function () {
        });

        it('Default state - Select no rows', function () {
            multiSelect.rows.push({id: 1});
            multiSelect.rows.push({id: 2});
            multiSelect.rows.push({id: 3});

            expect(multiSelect.allSelected()).toBeFalsy();
            expect(multiSelect.excluded()).toEqual([]);
            expect(multiSelect.selected()).toEqual([]);
        });

        it('Select specific several rows on several pages', function () {
            multiSelect.selected.push(4);
            multiSelect.selected.push(5);

            expect(multiSelect.allSelected()).toBeFalsy();
            expect(multiSelect.excluded()).toEqual([]);
            expect(multiSelect.selected()).toEqual([4, 5]);
        });

        it('Select all rows on several pages', function () {
            multiSelect.rows([
                {id: 1},
                {id: 2}
            ]);
            multiSelect.selectPage();
            multiSelect.rows([
                {id: 3},
                {id: 4}
            ]);
            multiSelect.selectPage();

            expect(multiSelect.allSelected()).toBeFalsy();
            expect(multiSelect.excluded()).toEqual([]);
            expect(multiSelect.selected()).toEqual([1, 2, 3, 4]);
        });

        it('Select all rows on current page with some specific rows on another page', function () {
            multiSelect.rows([
                {id: 1},
                {id: 2}
            ]);
            multiSelect.rows([
                {id: 3},
                {id: 4}
            ]);
            multiSelect.selectPage();
            multiSelect.rows([
                {id: 5},
                {id: 6}
            ]);
            multiSelect.selected.push(6);
            expect(multiSelect.allSelected()).toBeFalsy();
            expect(multiSelect.excluded()).toEqual([5]);
            expect(multiSelect.selected()).toEqual([3, 4, 6]);
        });

        it('Select all rows on several pages without some specific rows', function () {
            multiSelect.rows([
                {id: 1},
                {id: 2}
            ]);
            multiSelect.rows([
                {id: 3},
                {id: 4}
            ]);
            multiSelect.selectPage();
            multiSelect.selected.remove(4); // remove second

            expect(multiSelect.allSelected()).toBeFalsy();
            expect(multiSelect.excluded()).toEqual([4]);
            expect(multiSelect.selected()).toEqual([3]);
        });

        it('Select all rows all over the Grid', function () {
            multiSelect.rows([
                {id: 1},
                {id: 2}
            ]);
            multiSelect.selectAll();
            multiSelect.rows([
                {id: 3},
                {id: 4}
            ]);

            expect(multiSelect.allSelected()).toBeFalsy();
            expect(multiSelect.excluded()).toEqual([]);
            expect(multiSelect.selected()).toEqual([3, 4, 1, 2]);
        });

        it('Select all rows all over the Grid without all rows on current page but with specific rows on another page',
            function () {
                multiSelect.rows([
                    {id: 1},
                    {id: 2}
                ]);
                multiSelect.rows([
                    {id: 3},
                    {id: 4}
                ]);
                multiSelect.selectAll();
                multiSelect.deselectPage();
                multiSelect.rows([
                    {id: 5},
                    {id: 6}
                ]);

                expect(multiSelect.allSelected()).toBeFalsy();
                expect(multiSelect.excluded()).toEqual([3, 4]);
                expect(multiSelect.selected()).toEqual([5, 6]);
            });
    });
});
