/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'Magento_ImportExport/js/form/product-image-import-mode'
], function ($, productImageImportMode) {
    'use strict';

    describe('Magento_ImportExport/js/form/product-image-import-mode', function () {
        var productEntityCode = 'catalog_product',
            behaviorAppend = 'append',
            defaultMode = 'add';

        describe('shouldShowProductImageImportMode', function () {
            it('returns true for catalog_product + append', function () {
                expect(productImageImportMode.shouldShowProductImageImportMode(
                    productEntityCode,
                    behaviorAppend,
                    productEntityCode,
                    behaviorAppend
                )).toBe(true);
            });

            it('returns false for catalog_product + replace', function () {
                expect(productImageImportMode.shouldShowProductImageImportMode(
                    productEntityCode,
                    'replace',
                    productEntityCode,
                    behaviorAppend
                )).toBe(false);
            });

            it('returns false for catalog_product + delete', function () {
                expect(productImageImportMode.shouldShowProductImageImportMode(
                    productEntityCode,
                    'delete',
                    productEntityCode,
                    behaviorAppend
                )).toBe(false);
            });

            it('returns false for non-product entity with append', function () {
                expect(productImageImportMode.shouldShowProductImageImportMode(
                    'customer',
                    behaviorAppend,
                    productEntityCode,
                    behaviorAppend
                )).toBe(false);
            });

            it('returns false when entity or behavior is empty', function () {
                expect(productImageImportMode.shouldShowProductImageImportMode(
                    '',
                    behaviorAppend,
                    productEntityCode,
                    behaviorAppend
                )).toBe(false);
                expect(productImageImportMode.shouldShowProductImageImportMode(
                    productEntityCode,
                    null,
                    productEntityCode,
                    behaviorAppend
                )).toBe(false);
                expect(productImageImportMode.shouldShowProductImageImportMode(
                    null,
                    null,
                    productEntityCode,
                    behaviorAppend
                )).toBe(false);
            });
        });

        describe('applyProductImageImportModeVisibility', function () {
            var $field, $fieldRow;

            beforeEach(function () {
                $field = $('<select id="product_image_import_mode">' +
                    '<option value="add">Add</option>' +
                    '<option value="replace">Replace</option>' +
                    '</select>');
                $fieldRow = $('<div class="field-product_image_import_mode no-display"></div>');
                $field.val('replace');
                $field.prop('disabled', true);
                $fieldRow.hide();
                $('body').append($fieldRow.append($field));
            });

            afterEach(function () {
                $fieldRow.remove();
                $field = null;
                $fieldRow = null;
            });

            it('enables and shows the field when visible', function () {
                productImageImportMode.applyProductImageImportModeVisibility(
                    $field,
                    $fieldRow,
                    true,
                    defaultMode
                );

                expect($field.prop('disabled')).toBe(false);
                expect($fieldRow.hasClass('no-display')).toBe(false);
                expect($fieldRow.is(':visible')).toBe(true);
                expect($field.val()).toBe('replace');
            });

            it('disables, hides, and resets value when not visible', function () {
                $field.prop('disabled', false);
                $fieldRow.removeClass('no-display').show();
                $field.val('replace');

                productImageImportMode.applyProductImageImportModeVisibility(
                    $field,
                    $fieldRow,
                    false,
                    defaultMode
                );

                expect($field.prop('disabled')).toBe(true);
                expect($field.val()).toBe(defaultMode);
                expect($fieldRow.hasClass('no-display')).toBe(true);
                expect($fieldRow.is(':visible')).toBe(false);
            });

            it('no-ops when field is missing', function () {
                expect(function () {
                    productImageImportMode.applyProductImageImportModeVisibility(
                        $(),
                        $fieldRow,
                        true,
                        defaultMode
                    );
                }).not.toThrow();
            });
        });

        describe('shouldShowProductImageDeleteUnused', function () {
            var replaceMode = 'replace';

            it('returns true only for catalog_product + append + replace image mode', function () {
                expect(productImageImportMode.shouldShowProductImageDeleteUnused(
                    productEntityCode,
                    behaviorAppend,
                    replaceMode,
                    productEntityCode,
                    behaviorAppend,
                    replaceMode
                )).toBe(true);
            });

            it('returns false when image mode is add', function () {
                expect(productImageImportMode.shouldShowProductImageDeleteUnused(
                    productEntityCode,
                    behaviorAppend,
                    defaultMode,
                    productEntityCode,
                    behaviorAppend,
                    replaceMode
                )).toBe(false);
            });

            it('returns false when import behavior is not append', function () {
                expect(productImageImportMode.shouldShowProductImageDeleteUnused(
                    productEntityCode,
                    'replace',
                    replaceMode,
                    productEntityCode,
                    behaviorAppend,
                    replaceMode
                )).toBe(false);
            });

            it('returns false for non-product entity', function () {
                expect(productImageImportMode.shouldShowProductImageDeleteUnused(
                    'customer',
                    behaviorAppend,
                    replaceMode,
                    productEntityCode,
                    behaviorAppend,
                    replaceMode
                )).toBe(false);
            });
        });

        describe('applyProductImageDeleteUnusedVisibility', function () {
            var $field, $fieldRow;

            beforeEach(function () {
                $field = $('<input type="checkbox" id="product_image_delete_unused" value="1"/>');
                $fieldRow = $('<div class="field-product_image_delete_unused no-display"></div>');
                $field.prop('checked', true);
                $field.prop('disabled', true);
                $fieldRow.hide();
                $('body').append($fieldRow.append($field));
            });

            afterEach(function () {
                $fieldRow.remove();
                $field = null;
                $fieldRow = null;
            });

            it('enables and shows the checkbox when visible', function () {
                productImageImportMode.applyProductImageDeleteUnusedVisibility($field, $fieldRow, true);

                expect($field.prop('disabled')).toBe(false);
                expect($fieldRow.hasClass('no-display')).toBe(false);
                expect($fieldRow.is(':visible')).toBe(true);
            });

            it('disables, hides, and unchecks when not visible', function () {
                $field.prop('disabled', false).prop('checked', true);
                $fieldRow.removeClass('no-display').show();

                productImageImportMode.applyProductImageDeleteUnusedVisibility($field, $fieldRow, false);

                expect($field.prop('disabled')).toBe(true);
                expect($field.prop('checked')).toBe(false);
                expect($fieldRow.hasClass('no-display')).toBe(true);
                expect($fieldRow.is(':visible')).toBe(false);
            });
        });
    });
});
