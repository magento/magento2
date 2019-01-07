/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/

define(['Magento_Bundle/js/components/bundle-checkbox', 'uiRegistry'], function (BundleCheckbox, registry) {
    'use strict';

    describe('Magento_Bundle/js/components/bundle-checkbox', function () {

        var unit,
            typeMap = {
                typeMap: {
                    select: 'radio',
                    radio: 'radio',
                    checkbox: 'checkbox',
                    multi: 'checkbox'
                }
            };

        beforeEach(function () {
            unit = new BundleCheckbox({
                dataScope: 'bundle-checkbox',
                elementTmpl: jasmine.createSpy(),
                clearValues: jasmine.createSpy()
            });
        });

        describe('test changeType method', function () {
            it('Do not clear values for "multi" select type', function () {
                spyOn(registry, 'get').and.returnValue(typeMap);
                spyOn(unit, 'checked').and.returnValue(false);

                unit.changeType('multi');

                expect(unit.prefer).toBe('checkbox');
                expect(unit.clearValues).not.toHaveBeenCalled();
            });

            it('Do not clear values for "radio" select type if item not checked', function () {
                spyOn(registry, 'get').and.returnValue(typeMap);
                spyOn(unit, 'checked').and.returnValue(false);

                unit.changeType('select');

                expect(unit.prefer).toBe('radio');
                expect(unit.clearValues).not.toHaveBeenCalled();
            });

            it('Clear values for "radio" select type', function () {
                spyOn(registry, 'get').and.returnValue(typeMap);
                spyOn(unit, 'checked').and.returnValue(true);

                unit.changeType('select');

                expect(unit.prefer).toBe('radio');
            });
        });
    });
});
