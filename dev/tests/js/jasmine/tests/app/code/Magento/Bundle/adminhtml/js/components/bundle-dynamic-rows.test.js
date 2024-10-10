/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * ************************************************************************
 */

/*eslint max-nested-callbacks: 0*/
define(['Magento_Bundle/js/components/bundle-dynamic-rows', 'uiRegistry', 'uiCollection'],
    function (BundleDynamicRows, registry, uiCollection) {
        'use strict';

        describe('Magento_Bundle/js/components/bundle-dynamic-rows', function () {
            let unit;

            beforeEach(function () {
                unit = new BundleDynamicRows({
                    dataScope: 'bundle-dynamic-rows',
                    label: 'Dynamic Rows',
                    collapsibleHeader: true,
                    columnsHeader: false,
                    deleteProperty: false,
                    addButton: false,
                    name: 'dynamic',
                    bundleSelectionsName: 'bundle_selections'
                });
            });

            describe('test removeBundleItemsFromOption method', function () {
                it('Check if bundle items are removed from option', function () {
                    let bundleSelections = new uiCollection;

                    bundleSelections._elems = {
                        clear: jasmine.createSpy('clear')
                    };

                    spyOn(bundleSelections, 'destroyChildren').and.callThrough();
                    spyOn(registry, 'get').and.returnValue(bundleSelections);
                    spyOn(unit, 'removeBundleItemsFromOption').and.callThrough();

                    unit.removeBundleItemsFromOption(1);

                    expect(registry.get).toHaveBeenCalledWith('dynamic.1.bundle_selections');
                    expect(bundleSelections.destroyChildren).toHaveBeenCalled();
                    expect(bundleSelections._elems.clear).toHaveBeenCalled();
                });
            });
        });
    });
