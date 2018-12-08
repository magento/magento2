/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Add selected swatch attributes to redirect url
     *
     * @see Magento_Catalog/js/catalog-add-to-cart
     */
    $('body').on('catalogCategoryAddToCartRedirect', function (event, data) {
        $(data.form).find('[name*="super"]').each(function (index, item) {
            var $item = $(item),
                attr;
<<<<<<< HEAD

            if ($item.attr('data-attr-name')) {
                attr = $item.attr('data-attr-name');
            } else {
                attr = $item.parent().attr('attribute-code');
            }
            data.redirectParameters.push(attr + '=' + $item.val());
=======

            if ($item.attr('data-attr-name')) {
                attr = $item.attr('data-attr-name');
            } else {
                attr = $item.parent().attr('attribute-code');
            }
            data.redirectParameters.push(attr + '=' + $item.val());

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        });
    });
});
