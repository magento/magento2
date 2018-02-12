/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require([
    'jquery'
], function ($) {
    'use strict';

    $('body').on('catalogCategoryAddToCartRedirect', function (event, data) {
        $(data.form).find('[name*="super"]').each(function (index, item) {
            var $item = $(item);

            data.redirectParameters.push($item.attr('data-attr-name') + '=' + $item.val());
        });
    });
});
