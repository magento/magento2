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

        describe('"initObservable" method', function () {
            it('Check for defined ', function () {
                expect(sortByObj.hasOwnProperty('initObservable')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof sortByObj.initObservable;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(sortByObj.initObservable()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof sortByObj.initObservable();
                expect(type).toEqual('object');
            });
        });

        describe('"preparedOptions" method', function () {
            it('Check for defined ', function () {
                expect(sortByObj.hasOwnProperty('preparedOptions')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof sortByObj.preparedOptions;
                expect(type).toEqual('function');
            });

            it('Check "options" array is empty if sortable is set false', function () {
                var columns = [{
                        sortable: false,
                        label: 'magento',
                        index: 'test'
                    }],
                    expectedValue = [];
                sortByObj.preparedOptions(columns);
                expect(sortByObj.options).toEqual(expectedValue);
            });

            it('Check "options" array is set the correct value', function () {
                var columns = [{
                        sortable: true,
                        label: 'magento',
                        index: 'test'
                    }],
                    expectedValue = [{
                        value: 'test',
                        label: 'magento'
                    }];
                sortByObj.preparedOptions(columns);
                expect(sortByObj.options).toEqual(expectedValue);
            });

            it('Check "isVisible" set true if column is sortable', function () {
                var columns = [{
                        sortable: true,
                        label: 'magento',
                        index: 'test'
                    }];
                spyOn(sortByObj, "isVisible").and.callFake(function () {
                    return true;
                });
                sortByObj.preparedOptions(columns);
                expect(sortByObj.isVisible).toHaveBeenCalled();
                expect(sortByObj.isVisible()).toBeTruthy();
            });

            it('Check "isVisible" set true if column is sortable', function () {
                var columns = [{
                    sortable: true,
                    label: 'magento',
                    index: 'test'
                }];
                spyOn(sortByObj, "isVisible").and.callFake(function () {
                    return false;
                });
                sortByObj.preparedOptions(columns);
                expect(sortByObj.isVisible).toHaveBeenCalled();
                expect(sortByObj.isVisible()).toBeFalsy();
            });
        });
        describe('"applyChanges" method', function () {
            it('Check for defined ', function () {
                expect(sortByObj.hasOwnProperty('applyChanges')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof sortByObj.applyChanges;
                expect(type).toEqual('function');
            });

            it('Check "selectedOption" method has been called', function () {
                spyOn(sortByObj, 'selectedOption');
                sortByObj.applyChanges();
                expect(sortByObj.selectedOption).toHaveBeenCalled();
            });

            it('Check "applied" method has been called', function () {
                spyOn(sortByObj, 'applied');
                sortByObj.applyChanges();
                expect(sortByObj.applied).toHaveBeenCalled();
            });
        });
    });
});
