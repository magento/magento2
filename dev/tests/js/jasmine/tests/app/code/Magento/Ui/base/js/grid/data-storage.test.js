/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
/*jscs:disable requireCamelCaseOrUpperCaseIdentifiers*/

define([
    'mageUtils',
    'Magento_Ui/js/grid/data-storage'
], function (utils, DataStorage) {
    'use strict';

    describe('Magento_Ui/js/grid/data-storage', function () {
        describe('costructor', function () {
            it('converts dataScope property to array', function () {
                var model = new DataStorage({
                    dataScope: 'magento'
                });

                expect(model.dataScope).toEqual(['magento']);
            });
        });

        describe('hasScopeChanged', function () {
            it('is function', function () {
                var model = new DataStorage({
                    dataScope: ''
                });

                expect(model.hasScopeChanged).toBeDefined();
                expect(typeof model.hasScopeChanged).toEqual('function');
            });

            it('returns false if no requests have been made', function () {
                var model = new DataStorage({
                    dataScope: ''
                });

                expect(model.hasScopeChanged()).toBeFalsy();
            });

            it('tells whether parameters defined in the dataScope property have changed', function () {
                var params, newParams, model;

                params = {
                    namespace: 'magento',
                    search: '',
                    filters: {
                        store_id: 0
                    },
                    sorting: {},
                    paging: {}
                };

                newParams = utils.extend({}, params, {
                    search: 'magento',
                    filters: {
                        store_id: 1
                    }
                });

                model = new DataStorage({
                    dataScope: 'filters.store_id'
                });

                model.cacheRequest({
                    totalRecords: 0
                }, params);

                expect(model.hasScopeChanged(params)).toBeFalsy();
                expect(model.hasScopeChanged(newParams)).toBeTruthy();
            });
        });
    });
});
