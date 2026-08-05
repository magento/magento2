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
        }
    };
});
