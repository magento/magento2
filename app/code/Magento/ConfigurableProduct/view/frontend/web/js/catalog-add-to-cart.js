/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
require([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Add selected configurable attributes to redirect url
     *
     * @see Magento_Catalog/js/catalog-add-to-cart
     */
    $('body').on('catalogCategoryAddToCartRedirect', function (event, data) {
        $(data.form).find('select[name*="super"]').each(function (index, item) {
            data.redirectParameters.push(item.config.id + '=' + $(item).val());
        });
    });
});
