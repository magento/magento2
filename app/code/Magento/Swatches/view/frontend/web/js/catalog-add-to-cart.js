/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require([
    'jquery'
], function ($) {
    'use strict';

    $('body').on('catalogCategoryAddToCartRedirect', function (event, data) {
        $(data.form).find('[name*="super"]').each(function (index, item) {
            debugger;
            var $item = $(item);

            data.redirectParameters.push($item.attr('data-attr-name') + '=' + $item.val());
        });
    });
});
