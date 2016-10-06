/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    $(function () {

        // disabled select only
        $('select#frontend_input:disabled').each(function () {
            var select = $(this),
                currentValue = select.find('option:selected').val(),
                enabledTypes = ['select', 'swatch_visual', 'swatch_text'],
                warning = $('<label>')
                    .hide()
                    .text($.mage.__('These changes affect all related products.'))
                    .addClass('mage-error')
                    .attr({
                        generated: true, for: select.attr('id')
                    }),

                /**
                 * Toggle hint about changes types
                 */
                toggleWarning = function () {
                    if (select.find('option:selected').val() === currentValue) {
                        warning.hide();
                    } else {
                        warning.show();
                    }
                },

                /**
                 * Remove unsupported options
                 */
                removeOption = function () {
                    if (!~enabledTypes.indexOf($(this).val())) {
                        $(this).remove();
                    }
                };

            // Check current type (allow only: select, swatch_visual, swatch_text)
            if (!~enabledTypes.indexOf(currentValue)) {
                return;
            }

            // Enable select and keep only available options (all other will be removed)
            select.removeAttr('disabled').find('option').each(removeOption);

            // Add warning on page and event for show/hide it
            select.after(warning).on('change', toggleWarning);
        });
    });
});
