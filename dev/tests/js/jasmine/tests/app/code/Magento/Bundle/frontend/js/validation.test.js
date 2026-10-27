/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_Bundle/js/validation',
    'mage/validation'
], function ($, bundleValidationMixin) {
    'use strict';

    describe('Magento_Bundle/js/validation', function () {
        var errorPlacement;

        beforeAll(function () {
            var originalWidget = $.mage.validation,
                originalBridge = $.fn.validation;

            errorPlacement = bundleValidationMixin(originalWidget).prototype.options.errorPlacement;
            $.mage.validation = originalWidget;
            $.fn.validation = originalBridge;
        });

        it('places the datepicker error next to the calendar button when one is present', function () {
            var wrapper = $('<div/>'),
                element = $('<input type="text" class="_has-datepicker"/>'),
                button = $('<button type="button"/>'),
                error = $('<div class="mage-error"/>');

            wrapper.append(element).append(button);

            errorPlacement.call({}, error, element);

            expect(button.next().is(error)).toBe(true);
        });

        it('places the datepicker error next to the field when there is no calendar button', function () {
            var wrapper = $('<div/>'),
                element = $('<input type="text" class="_has-datepicker"/>'),
                error = $('<div class="mage-error"/>');

            wrapper.append(element);

            errorPlacement.call({}, error, element);

            expect(element.next().is(error)).toBe(true);
        });
    });
});
