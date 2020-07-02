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
                setData: jasmine.createSpy(),
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
            it('return object from url with another parameter', function () {
                var urlSearch = '?anotherparam=1';

                expect(urlFilterApplierObj.getFilterParam(urlSearch)).toEqual({});
            });
        });

        describe('"apply" method', function () {
            it('applies url filter on filter component', function () {
                urlFilterApplierObj.searchString = '?filters[name]=test&filters[qty]=1';
                urlFilterApplierObj.apply();
                expect(urlFilterApplierObj.filterComponent().setData).toHaveBeenCalledWith({
                    'name': 'test',
                    'qty': '1'
                }, false);
                expect(urlFilterApplierObj.filterComponent().apply).toHaveBeenCalled();
            });
        });
    });
});
