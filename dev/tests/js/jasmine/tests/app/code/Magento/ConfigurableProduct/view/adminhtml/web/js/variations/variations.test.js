/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_ConfigurableProduct/js/variations/variations'
], function (Variations) {
    'use strict';

    describe('Magento_ConfigurableProduct/js/variations/variations', function () {
        var variation;

        beforeEach(function () {
            variation = new Variations();

            variation.source = {
                data: {}
            };
        });

        it('checks that "serializeData" serializes data', function () {
            var matrix = [
                    {
                        name: 'Product1',
                        attributes: 'Color: black',
                        price: 100
                    },
                    {
                        name: 'Product2',
                        attributes: 'Color: red',
                        price: 50
                    },
                    {
                        name: 'Product3',
                        attributes: 'Color: white',
                        price: 20
                    }
                ],
                ids = [1, 2, 3],
                resultMatrix = JSON.stringify(matrix),
                resultIds = JSON.stringify(ids);

            variation.source.data['configurable-matrix'] = matrix;
            variation.source.data['associated_product_ids'] = ids;

            variation.serializeData();

            expect(variation.source.data['configurable-matrix']).toEqual(matrix);
            expect(variation.source.data['associated_product_ids']).toEqual(ids);
            expect(variation.source.data['configurable-matrix-serialized']).toEqual(resultMatrix);
            expect(variation.source.data['associated_product_ids_serialized']).toEqual(resultIds);
        });

        it('checks that "serializeData" uses old data if there is no data to serialize', function () {

            var matrix = [
                    {
                        name: 'Product4',
                        attributes: 'Color: grey',
                        price: 5
                    },
                    {
                        name: 'Product5',
                        attributes: 'Color: pink',
                        price: 70
                    },
                    {
                        name: 'Product6',
                        attributes: 'Color: brown',
                        price: 30
                    }
                ],
                ids = [4, 5, 6],
                resultMatrix = JSON.stringify(matrix),
                resultIds = JSON.stringify(ids);

            variation.source.data['configurable-matrix-serialized'] = JSON.stringify(matrix);
            variation.source.data['associated_product_ids_serialized'] = JSON.stringify(ids);

            variation.serializeData();

            expect(variation.source.data['configurable-matrix-serialized']).toEqual(resultMatrix);
            expect(variation.source.data['associated_product_ids_serialized']).toEqual(resultIds);
        });

        it('checks that "serializeData" works correctly if we have new data to be serialized', function () {
            var matrix = [
                    {
                        name: 'Product7',
                        attributes: 'Color: yellow',
                        price: 10
                    },
                    {
                        name: 'Product8',
                        attributes: 'Color: green',
                        price: 200
                    },
                    {
                        name: 'Product9',
                        attributes: 'Color: blue',
                        price: 500
                    }
                ],
                ids = [7, 8, 9],
                resultMatrix = JSON.stringify(matrix),
                resultIds = JSON.stringify(ids);

            variation.source.data['configurable-matrix'] = matrix;
            variation.source.data['associated_product_ids'] = ids;
            variation.source.data['configurable-matrix-serialized'] = JSON.stringify(['some old data']);
            variation.source.data['associated_product_ids_serialized'] = JSON.stringify(['some old data']);
            variation.serializeData();

            expect(variation.source.data['configurable-matrix']).toEqual(matrix);
            expect(variation.source.data['associated_product_ids']).toEqual(ids);
            expect(variation.source.data['configurable-matrix-serialized']).toEqual(resultMatrix);
            expect(variation.source.data['associated_product_ids_serialized']).toEqual(resultIds);
        });

        it('checks that "unserializeData" unserializes data', function () {
            var matrixString = '[{"name":"Small Product","attributes":"Size: small","price":5.5},' +
                '{"name":"Medium Product","attributes":"Size: medium","price":10.99},' +
                '{"name":"Large Product","attributes":"Size: large","price":25}]',
            idString = '[100, 101, 102]',
            resultMatrix = JSON.parse(matrixString),
            resultIds = JSON.parse(idString);

            variation.source.data['configurable-matrix-serialized'] = matrixString;
            variation.source.data['associated_product_ids_serialized'] = idString;

            variation.unserializeData();

            expect(variation.source.data['configurable-matrix-serialized']).toBeUndefined();
            expect(variation.source.data['associated_product_ids_serialized']).toBeUndefined();
            expect(variation.source.data['configurable-matrix']).toEqual(resultMatrix);
            expect(variation.source.data['associated_product_ids']).toEqual(resultIds);
        });

        it('checks that "serializeData" and "unserializeData" give proper result', function () {
            var matrix = [
                    {
                        name: 'Small Product',
                        attributes: 'Size: small',
                        price: 5.50
                    },
                    {
                        name: 'Medium Product',
                        attributes: 'Size: medium',
                        price: 10.99
                    },
                    {
                        name: 'Large Product',
                        attributes: 'Size: large',
                        price: 25
                    }
                ],
                ids = [1, 2, 3],
                resultMatrix = JSON.stringify(matrix),
                resultIds = JSON.stringify(ids);

            variation.source.data['configurable-matrix'] = matrix;
            variation.source.data['associated_product_ids'] = ids;

            variation.serializeData();

            expect(variation.source.data['configurable-matrix']).toEqual(matrix);
            expect(variation.source.data['associated_product_ids']).toEqual(ids);
            expect(variation.source.data['configurable-matrix-serialized']).toEqual(resultMatrix);
            expect(variation.source.data['associated_product_ids_serialized']).toEqual(resultIds);

            variation.unserializeData();

            expect(variation.source.data['configurable-matrix']).toEqual(matrix);
            expect(variation.source.data['associated_product_ids']).toEqual(ids);
            expect(variation.source.data['configurable-matrix-serialized']).toBeUndefined();
            expect(variation.source.data['associated_product_ids_serialized']).toBeUndefined();
        });
    });
});
