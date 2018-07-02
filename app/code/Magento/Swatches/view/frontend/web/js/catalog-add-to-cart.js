/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require([
    'jquery'
], function ($) {
    'use strict';

    $('body').on('catalogCategoryAddToCartRedirect', function (event, data) {
        $(data.form).find('[name*="super"]').each(function (index, item) {
            var $item = $(item),
                attr;

            if ($item.attr('data-attr-name')) {
                attr = $item.attr('data-attr-name');
            } else {
                attr = $item.parent().attr('attribute-code');
            }
            data.redirectParameters.push(attr + '=' + $item.val());

        });
    });
});
