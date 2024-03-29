/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ***********************************************************************
 */

/*eslint max-nested-callbacks: 0*/

define(['Magento_Bundle/js/components/bundle-user-defined-checkbox'], function (BundleUserDefinedCheckbox) {
    'use strict';

    describe('Magento_Bundle/js/components/bundle-user-defined-checkbox', function () {
        let BundleUserDefinedCheckboxObj;

        beforeEach(function () {
            BundleUserDefinedCheckboxObj = new BundleUserDefinedCheckbox({
                dataScope: 'bundle-user-defined-checkbox'
            });
        });

        afterEach(function () {
            BundleUserDefinedCheckboxObj = null;
        });

        describe('The user defined a checkbox for the input type change method in the test bundle.', function () {

            it('verify the object that needs to be defined', function () {
                expect(BundleUserDefinedCheckboxObj).toBeDefined();
            });

            it('test the default values to ensure they are correct.', function () {
                expect(BundleUserDefinedCheckboxObj.inputType).toBeUndefined();
                expect(BundleUserDefinedCheckboxObj.visible()).toBe(true);
            });

            it('when using checkbox or multi-select input types, elements should be hidden.', function () {
                spyOn(BundleUserDefinedCheckboxObj, 'reset').and.returnValue(BundleUserDefinedCheckboxObj);

                BundleUserDefinedCheckboxObj.onInputTypeChange('checkbox');
                expect(BundleUserDefinedCheckboxObj.reset).toHaveBeenCalled();
                expect(BundleUserDefinedCheckboxObj.visible()).toBe(false);

                BundleUserDefinedCheckboxObj.onInputTypeChange('multi');
                expect(BundleUserDefinedCheckboxObj.reset.calls.count()).toBe(2);
                expect(BundleUserDefinedCheckboxObj.visible()).toBe(false);
            });

            it('the element should be visible when the input type is not a checkbox or multi-line.', function () {
                spyOn(BundleUserDefinedCheckboxObj, 'visible');

                BundleUserDefinedCheckboxObj.onInputTypeChange('text');
                expect(BundleUserDefinedCheckboxObj.visible).toHaveBeenCalledWith(true);

                BundleUserDefinedCheckboxObj.onInputTypeChange('radio');
                expect(BundleUserDefinedCheckboxObj.visible.calls.count()).toBe(2);
                expect(BundleUserDefinedCheckboxObj.visible).toHaveBeenCalledWith(true);
            });
        });
    });
});
