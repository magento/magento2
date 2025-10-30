/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

/* eslint-disable max-nested-callbacks */
define([
    'Magento_Ui/js/lib/core/collection',
    'Magento_Ui/js/lib/core/element/element'
], function (uiCollection, uiElement) {
    'use strict';

    describe('Magento_Ui/js/lib/core/collection', function () {
        describe('"insertChild" method', function () {
            it('should not slow down due to large position value', function () {
                const items = [
                    {name: 'elem-1', position: 2},
                    {name: 'elem-2', position: 0},
                    {name: 'elem-3', position: 1},
                    {name: 'elem-4', position: 5},
                    {name: 'elem-5', position: 9},
                    {name: 'elem-6', position: 3},
                    {name: 'elem-7', position: 8},
                    {name: 'elem-8', position: 4},
                    {name: 'elem-9', position: 7}
                ];

                let collection,
                    maxExecutionTime = 0,
                    startTime;

                // Measure the maximum time taken to insert items with small position values
                for (let i = 0; i < 10; i++) {
                    let tmpCollection = new uiCollection();

                    startTime = performance.now();
                    items.forEach(function (item) {
                        tmpCollection.insertChild(new uiElement({name: item.name}), item.position);
                    });
                    maxExecutionTime = Math.max(maxExecutionTime, performance.now() - startTime);
                }

                // Measure the time taken to insert items with a large position value
                items[0].position = 9999999;
                collection = new uiCollection();
                startTime = performance.now();
                items.forEach(function (item) {
                    collection.insertChild(new uiElement({name: item.name}), item.position);
                });

                // Verify that the time taken is not significantly longer than the normal execution time
                // This used to be around 6000ms versus 5ms (1000x slower).
                // But now it takes approximately the same time as normal execution.
                // Setting a threshold of 5 times the normal execution time to account for fluctuations.
                expect(performance.now() - startTime).toBeLessThan(5 * maxExecutionTime);

                // Verify that the items are sorted correctly
                expect(
                    collection.elems().map(function (elem) {
                        return elem.name;
                    })
                ).toEqual(
                    items.slice()
                        .sort(function (a, b) {
                            return a.position - b.position;
                        })
                        .map(function (elem) {
                            return elem.name;
                        })
                );
            }, 1000);
        });
    });
});
