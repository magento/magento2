/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/color-picker'
], function (ColorPicker) {
    'use strict';

    describe('ColorPicker UI Component Form Element', function () {
        it('Should have colorPickerConfig.value set to UI component instance\'s value', function () {
            var colorPicker = new ColorPicker({
                dataScope: ''
            });

            expect(colorPicker.colorPickerConfig.value).toBe(colorPicker.value);
        });
    });
});
