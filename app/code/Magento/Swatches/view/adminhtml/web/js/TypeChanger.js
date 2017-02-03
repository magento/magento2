/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require([
    "jquery"
], function ($) {
    $(function () {

        // disabled select only
        $('select#frontend_input:disabled').each(function () {
            var $select = $(this),
                value = $select.find('option:selected').val(),
                enabledTypes = ['select', 'swatch_visual', 'swatch_text'],
                message = 'This changes affect all related products';

            // Check current type (allow only: select, swatch_visual, swatch_text)
            if (enabledTypes.indexOf(value) < 0) {
                return;
            }

            // Enable select and keep only available options (all other will be removed)
            $select
                .removeAttr('disabled')
                .find('option').each(function () {
                    var $option = $(this);
                    if (enabledTypes.indexOf($option.val()) < 0) {
                        $option.remove();
                    }
                });

            // Create warning container
            var $warning = $('<label>').hide().text(message).addClass('mage-error').attr({
                generated: true,
                for: $select.attr('id')
            });

            // Add warning on page and event for show/hide it
            $select
                .after($warning)
                .on('change', function () {
                    if ($select.find('option:selected').val() == value) {
                        $warning.hide();
                    } else {
                        $warning.show();
                    }
                })
        });
    });
});
