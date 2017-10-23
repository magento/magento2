/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define([
    'Magento_Catalog/js/components/dynamic-rows-import-custom-options'
], function (DynamicRows) {
    'use strict';

    describe('Magento_Catalog/js/components/dynamic-rows-import-custom-options', function () {
        var model, data;

        beforeEach(function () {
            model = new DynamicRows({
                index: 'dynamic_rows',
                name: 'dynamic_rows',
                indexField: 'id',
                dataScope: '',
                rows: [{
                    identifier: 'row'
                }]
            });
            data = [{
                'options': [
                    {
                        'sort_order': 1,
                        'option_id': 1,
                        'option_type_id': 1,
                        'values': [{
                            'option_id': 1,
                            'option_type_id': 1,
                            'some_fake_value': 1
                        }]
                    },
                    {
                        'sort_order': 2,
                        'option_id': 2,
                        'option_type_id': 2,
                        'values': [{
                            'option_id': 2,
                            'option_type_id': 2
                        }]
                    }
                ]
            }];
            model.source = {
                set: jasmine.createSpy()
            };
            model.insertData = jasmine.createSpy().and.returnValue([]);
        });

        describe('Check processingInsertData', function () {
            it('Check with empty data.', function () {
                model.processingInsertData();
                expect(model.cacheGridData).toEqual([]);
                expect(model.insertData).not.toHaveBeenCalled();
            });

            it('Check with empty options data.', function () {
                data = [{
                    'options': []
                }];
                model.processingInsertData(data);
                expect(model.cacheGridData).toEqual([]);
                expect(model.insertData).not.toHaveBeenCalled();
            });

            it('Check with fake imported custom options data.', function () {
                model.processingInsertData(data);
                expect(model.insertData).toHaveBeenCalled();
                expect(model.cacheGridData[0]).toEqual({
                    'option_type_id': 1,
                    'position': 1,
                    'values': [{
                        'some_fake_value': 1
                    }]
                });
                expect(model.cacheGridData[1]).toEqual({
                    'option_type_id': 2,
                    'position': 2,
                    'values': [{}]
                });
            });
        });
    });
});
