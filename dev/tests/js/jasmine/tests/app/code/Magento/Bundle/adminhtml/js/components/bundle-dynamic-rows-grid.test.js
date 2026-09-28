/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * ************************************************************************
 */
/*eslint max-nested-callbacks: 0*/
define(['Magento_Bundle/js/components/bundle-dynamic-rows-grid'],
    function (BundleDynamicRowsGrid) {
        'use strict';

        describe('Magento_Bundle/js/components/bundle-dynamic-rows-grid', function () {
            let dynamicRowsGrid;

            beforeEach(function () {
                dynamicRowsGrid = new BundleDynamicRowsGrid();
            });

            describe('test parseProcessingAddChild method', function () {
                it('Check the processingAddChild method should call when recordIndex is a valid number', function () {
                    let data = [4], newData = [4];

                    spyOn(dynamicRowsGrid, 'processingAddChild').and.callThrough();

                    dynamicRowsGrid.parseProcessingAddChild(data, newData);

                    expect(dynamicRowsGrid.processingAddChild).toHaveBeenCalled();
                });

                it('Check the processingAddChild method should not call when recordIndex is inValid number',
                    function () {
                        let data = NaN, newData = [2];

                        spyOn(dynamicRowsGrid, 'processingAddChild').and.callThrough();

                        dynamicRowsGrid.parseProcessingAddChild(data, newData);

                        expect(dynamicRowsGrid.processingAddChild).not.toHaveBeenCalled();
                    });
            });
        });
    });
