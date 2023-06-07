/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'Magento_Ui/js/grid/url-filter-applier'
], function (UrlFilterApplier) {
    'use strict';

    describe('Magento_Ui/js/grid/url-filter-applier', function () {
        var urlFilterApplierObj,
            filterComponentMock = {
                set: jasmine.createSpy(),
                get: jasmine.createSpy(),
                apply: jasmine.createSpy()
            };

        beforeEach(function () {
            urlFilterApplierObj = new UrlFilterApplier({});
            urlFilterApplierObj.filterComponent = jasmine.createSpy().and.returnValue(filterComponentMock);
        });

        describe('"getFilterParam" method', function () {
            it('return object from url with a simple filters parameter', function () {
                var urlSearch = '?filters[name]=test';

                expect(urlFilterApplierObj.getFilterParam(urlSearch)).toEqual({
                    'name': 'test'
                });
            });
            it('return object from url with multiple filters parameter', function () {
                var urlSearch = '?filters[name]=test&filters[qty]=1';

                expect(urlFilterApplierObj.getFilterParam(urlSearch)).toEqual({
                        'name': 'test',
                        'qty': '1'
                    });
            });
            it('return object from url with multiple filters parameter and another parameter', function () {
                var urlSearch = '?filters[name]=test&filters[qty]=1&anotherparam=1';

                expect(urlFilterApplierObj.getFilterParam(urlSearch)).toEqual({
                    'name': 'test',
                    'qty': '1'
                });
            });
            it('return object from url with multiple filters parameter and filter value as array', function () {
                var urlSearch = '?filters[name]=[27,23]&filters[qty]=1&anotherparam=1';

                expect(urlFilterApplierObj.getFilterParam(urlSearch)).toEqual({
                    'name': ['27', '23'],
                    'qty': '1'
                });
            });
            it('return object from url with another parameter', function () {
                var urlSearch = '?anotherparam=1';

                expect(urlFilterApplierObj.getFilterParam(urlSearch)).toEqual({});
            });
        });

        describe('"apply" method', function () {
            it('applies url filter on filter component', function () {
                urlFilterApplierObj.searchString = '?filters[name]=test&filters[qty]=1';
                urlFilterApplierObj.apply();
                expect(urlFilterApplierObj.filterComponent().get).toHaveBeenCalled();
                expect(urlFilterApplierObj.filterComponent().set).toHaveBeenCalledWith(
                    'applied',
                    {
                        'name': 'test',
                        'qty': '1'
                    }
                );
            });
        });
    });
});
