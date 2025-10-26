/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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

            if ($item.attr('data-attr-name')) {
                attr = $item.attr('data-attr-name');
            } else {
                attr = $item.parent().attr('attribute-code');
            }
            data.redirectParameters.push(attr + '=' + $item.val());

        });
    });
});
