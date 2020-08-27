/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Catalog/js/product/view/product-info'
], function (_, productInfo) {
    'use strict';

    /**
     * Returns info about products in form.
     *
     * @param {jQuery} $form
     * @return {Array}
     */
    return function ($form) {
        var product = _.findWhere($form.serializeArray(), {
                name: 'product'
            });

        if (!_.isUndefined(product)) {
            productInfo().push(
                {
                    'id': product.value
                }
            );
        }

        return _.uniq(productInfo(), function (item) {
            return item.id;
        });
    };
});

