/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
define([
    'underscore',
    'Magento_Catalog/js/product/view/product-ids'
], function (_, productIds) {
    'use strict';

    /**
     * Returns id's of products in form.
     *
     * @param {jQuery} $form
     * @return {Array}
     */
    return function ($form) {
        var idSet = productIds(),
            product = _.findWhere($form.serializeArray(), {
            name: 'product'
        });

        if (!_.isUndefined(product)) {
            idSet.push(product.value);
        }

        return _.uniq(idSet);
    };
});
