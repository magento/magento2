/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'Magento_Ui/js/grid/sortBy'
], function (SortBy) {
    'use strict';

    describe('Magento_Ui/js/grid/sortBy', function () {

        var sortByObj;

        beforeEach(function () {
            sortByObj = new SortBy({
                options: []
            });
        });

        describe('"preparedOptions" method', function () {
            it('sort option will not available if sorting is disabled for the columns', function () {
                var columns = {
                    sortable: false,
                    label: 'magento',
                    index: 'name'
                };

                sortByObj.preparedOptions([columns]);
                expect(sortByObj.options[0]).toBeUndefined();
                expect(sortByObj.options[0]).toBeUndefined();
            });

            it('sort option will available if sorting is enabled for the columns', function () {
                var columns = {
                    sortable: true,
                    label: 'magento',
                    index: 'name'
                };

                sortByObj.preparedOptions([columns]);
                expect(sortByObj.options[0].value).toEqual('name');
                expect(sortByObj.options[0].label).toEqual('magento');
            });

            it('return "isVisible" method true if sorting is enabled for column', function () {
                var columns = {
                    sortable: true,
                    label: 'magento',
                    index: 'name'
                };

                sortByObj.preparedOptions([columns]);
                expect(sortByObj.isVisible()).toBeTruthy();
            });

            it('return "isVisible" method false if sorting is disabled for column', function () {
                var columns = {
                    sortable: false,
                    label: 'magento',
                    index: 'name'
                };

                sortByObj.preparedOptions([columns]);
                expect(sortByObj.isVisible()).toBeFalsy();
            });
        });
        describe('"applyChanges" method', function () {
            it('return applied options for sorting column', function () {
                var applied = {
                    field: 'selectedOption',
                    direction: 'asc'
                };

                spyOn(sortByObj, 'selectedOption').and.returnValue('selectedOption');
                sortByObj.applyChanges();
                expect(sortByObj.applied()).toEqual(applied);
                expect(sortByObj.selectedOption).toHaveBeenCalled();
            });
        });
    });
});
