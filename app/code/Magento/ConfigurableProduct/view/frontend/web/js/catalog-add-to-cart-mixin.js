/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'jquery',
    'Magento_ConfigurableProduct/js/product/view/product-info-resolver'
], function (_, $, productInfoResolver) {
    'use strict';

    return function (widget) {

        $.widget('mage.catalogAddToCart', widget, {
            /**
             * @param {jQuery} form
             */
            ajaxSubmit: function (form) {
                var isConfigurable = !!_.find(form.serializeArray(), function (item) {
                    return item.name.indexOf('super_attribute') !== -1;
                });

                if (isConfigurable) {
                    this.options.productInfoResolver = productInfoResolver;
                }

                return this._super(form);
            }
        });

        return $.mage.catalogAddToCart;
    };
});
