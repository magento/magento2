/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Catalog/js/product/view/product-ids'
], function ($, productIds) {
    'use strict';

    /**
     * Returns id's of products in form.
     *
     * @param {Object} config
     * @param {HTMLElement} element
     * @return {Array}
     */
    return function (config, element) {
        $(element).find('div[data-product-id]').each(function () {
            productIds.push($(this).data('productId').toString());
        });

        return productIds();
    };
});
