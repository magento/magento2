/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
define([], function () {
    'use strict';

    return {
        shouldShowProductImageImportMode: function (entity, behavior, productEntityCode, behaviorAppend) {
            return entity === productEntityCode && behavior === behaviorAppend;
        },

        shouldShowProductImageDeleteUnused: function (
            entity,
            behavior,
            imageMode,
            productEntityCode,
            behaviorAppend,
            replaceMode
        ) {
            return this.shouldShowProductImageImportMode(entity, behavior, productEntityCode, behaviorAppend)
                && imageMode === replaceMode;
        },

        applyProductImageImportModeVisibility: function ($field, $fieldRow, isVisible, defaultMode) {
            if (!$field || !$field.length) {
                return;
            }

            if (isVisible) {
                $field.prop('disabled', false).show();

                if ($fieldRow && $fieldRow.length) {
                    $fieldRow.removeClass('no-display').show();
                }
            } else {
                $field.val(defaultMode);
                $field.prop('disabled', true).hide();

                if ($fieldRow && $fieldRow.length) {
                    $fieldRow.addClass('no-display').hide();
                }
            }
        },

        applyProductImageDeleteUnusedVisibility: function ($field, $fieldRow, isVisible) {
            if (!$field || !$field.length) {
                return;
            }

            if (isVisible) {
                $field.prop('disabled', false).show();

                if ($fieldRow && $fieldRow.length) {
                    $fieldRow.removeClass('no-display').show();
                }
            } else {
                $field.prop('checked', false);
                $field.prop('disabled', true).hide();

                if ($fieldRow && $fieldRow.length) {
                    $fieldRow.addClass('no-display').hide();
                }
            }
        }
    };
});
