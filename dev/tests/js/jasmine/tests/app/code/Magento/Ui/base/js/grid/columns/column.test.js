/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    describe('Ui/js/grid/columns/column', function () {
        var column;

        beforeEach(function () {
            column = new Column({
                sortable: true,
                sorting: false,
                headerTmpl: 'header',
                bodyTmpl: 'body',

                /** Stub */
                source: function () {}
            });
        });

        describe('sort method', function () {
            it('apply sorting first time', function () {
                column.sort(true);
                expect(column.sorting).toBe('asc');
            });

            it('remove sorting', function () {
                column.sort(false);
                expect(column.sorting).toBeFalsy();
            });
        });

        describe('applyFieldAction method', function () {
            it('apply field action if action do not exists', function () {
                spyOn(column, '_getFieldCallback');

                column.applyFieldAction(1);
                expect(column._getFieldCallback).not.toHaveBeenCalled();
            });

            it('apply field action if action is disabled', function () {
                spyOn(column, '_getFieldCallback');
                column.disableAction = true;

                column.applyFieldAction(1);
                expect(column._getFieldCallback).not.toHaveBeenCalled();
            });

            it('apply field action if action exists', function () {
                var isCallbackCalled;

                column.fieldAction = {};
                spyOn(column, '_getFieldCallback').and.returnValue(function () {
                    isCallbackCalled = true;
                });

                column.applyFieldAction(1);

                expect(column._getFieldCallback).toHaveBeenCalled();
                expect(isCallbackCalled).toBeTruthy();
            });
        });

        describe('get templates methods', function () {
            it('getHeader', function () {
                expect(column.getHeader()).toBe(column.headerTmpl);
            });

            it('getBody', function () {
                expect(column.getBody()).toBe(column.bodyTmpl);
            });
        });
    });
});
