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
     * Returns info about configurable products in form.
     *
     * @param {jQuery} $form
     * @return {Array}
     */
    return function ($form) {
        var optionValues = [],
            product = _.findWhere($form.serializeArray(), {
                name: 'product'
            }),
            productId;

        if (!_.isUndefined(product)) {
            productId = product.value;
            _.each($form.serializeArray(), function (item) {
                if (item.name.indexOf('super_attribute') !== -1) {
                    optionValues.push(item.value);
                }
            });
            optionValues.sort();
            productInfo().push(
                {
                    'id': productId,
                    'optionValues': optionValues
                }
            );
        }

        return _.uniq(productInfo(), function (item) {
            var optionValuesStr = item.optionValues ? item.optionValues.join() : '';

            return item.id + optionValuesStr;
        });
    };
});

