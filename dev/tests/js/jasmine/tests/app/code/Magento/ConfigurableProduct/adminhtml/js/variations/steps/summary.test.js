/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


/* eslint max-nested-callbacks: 0 */
/* jscs:disable jsDoc*/

define([
    'Magento_ConfigurableProduct/js/variations/steps/summary'
], function (Summary) {
    'use strict';

    describe('Magento_ConfigurableProduct/js/variations/steps/summary', function () {
        let model, quantityFieldName, productDataFromGrid, productDataFromGridExpected;

        beforeEach(function () {
            quantityFieldName = 'quantity123';
            model = new Summary({quantityFieldName: quantityFieldName});

            productDataFromGrid = {
                sku: 'testSku',
                name: 'test name',
                weight: 12.12312,
                status: 1,
                price: 333.333,
                someField: 'someValue',
                quantity: 10
            };

            productDataFromGrid[quantityFieldName] = 12;

            productDataFromGridExpected = {
                sku: 'testSku',
                name: 'test name',
                weight: 12.12312,
                status: 1,
                price: 333.333
            };
        });

        describe('Check prepareProductDataFromGrid', function () {

            it('Check call to prepareProductDataFromGrid method with qty', function () {
                productDataFromGrid.qty = 3;
                productDataFromGridExpected[quantityFieldName] = 3;
                const result = model.prepareProductDataFromGrid(productDataFromGrid);

                expect(result).toEqual(productDataFromGridExpected);
            });


            it('Check call to prepareProductDataFromGrid method without qty', function () {
                const result = model.prepareProductDataFromGrid(productDataFromGrid);

                expect(result).toEqual(productDataFromGridExpected);
            });
        });
    });
});
