/**
 * Copyright © 2017 Magento. All rights reserved.
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
        describe('initConfig', function () {
            it('is function', function () {
                var model = new DataStorage({
                    dataScope: ''
                });

                expect(model.initConfig).toBeDefined();
                expect(typeof model.initConfig).toEqual('function');
            });

            it('Check method change "$this.dataScope" property', function () {
                var model = new DataStorage({
                    dataScope: 'magento'
                });

                model.initConfig;
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

            it('check if requests have been made', function () {
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
                    dataScope: ['filters.store_id'] //became after initConfig method call
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
