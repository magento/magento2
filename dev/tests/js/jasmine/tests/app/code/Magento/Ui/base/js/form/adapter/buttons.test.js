/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define(['Magento_Ui/js/form/adapter/buttons'], function (uiFormSelectors) {
    'use strict';
    describe('UI Form Selectors Module', function () {

        it('should define reset, save, and saveAndContinue selectors', function () {
            // Check that the properties are defined
            expect(uiFormSelectors).toBeDefined();
            expect(uiFormSelectors.reset).toBeDefined();
            expect(uiFormSelectors.save).toBeDefined();
            expect(uiFormSelectors.saveAndContinue).toBeDefined();

            // Verify that each selector matches the expected value
            expect(uiFormSelectors.reset).toBe('#reset_ui_form');
            expect(uiFormSelectors.save).toBe('#save');
            expect(uiFormSelectors.saveAndContinue).toBe('#save_and_continue');
        });

    });
});
