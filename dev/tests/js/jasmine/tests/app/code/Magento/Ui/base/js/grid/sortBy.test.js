define([
    'Magento_Ui/js/grid/sortBy'
], function (sortBy) {
    'use strict';
    describe('Magento_Ui/js/grid/sortBy', function () {

        var sortByObj;

        beforeEach(function () {
            sortByObj = new sortBy({
                options: []
            });
        });

        describe('"preparedOptions" method', function () {
            it('return empty array if sorting is disabled for the columns', function () {
                var columns = [{
                        sortable: false,
                        label: 'magento',
                        index: 'name'
                    }],
                    options = [];
                sortByObj.preparedOptions(columns);
                expect(sortByObj.options).toEqual(options);
            });

            it('return array of options if sorting is enabled for the columns', function () {
                var columns = [{
                        sortable: true,
                        label: 'magento',
                        index: 'name'
                    }],
                    options = [{
                        value: 'name',
                        label: 'magento'
                    }];
                sortByObj.preparedOptions(columns);
                expect(sortByObj.options).toEqual(options);
            });

            it('return "isVisible" method true if column is sortable', function () {
                var columns = [{
                        sortable: true,
                        label: 'magento',
                        index: 'name'
                    }];
                sortByObj.preparedOptions(columns);
                expect(sortByObj.isVisible()).toBeTruthy();
            });

            it('return "isVisible" method false if column is sortable', function () {
                var columns = [{
                    sortable: false,
                    label: 'magento',
                    index: 'name'
                }];
                sortByObj.preparedOptions(columns);
                expect(sortByObj.isVisible()).toBeFalsy();
            });
        });
        describe('"applyChanges" method', function () {
            it('return applied option', function () {
                var applied = {
                    field: 'selectedOption',
                    direction: 'desc'
                };
                spyOn(sortByObj, 'selectedOption').and.returnValue('selectedOption');
                sortByObj.applyChanges();
                expect(sortByObj.applied()).toEqual(applied);
                expect(sortByObj.selectedOption).toHaveBeenCalled();
            });
        });
    });
});
