/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'mage/translate'
], function (Component, $, _) {
    'use strict';
    var mixin = {

        /**
         *
         * @inheritDoc
         */
        initObservable: function () {
            this._super();
            this.quantityFieldName = 'quantity_per_source';
            this.attributesName = [
                $.mage.__('Images'),
                $.mage.__('SKU'),
                $.mage.__('Quantity Per Source'),
                $.mage.__('Price')
            ];

            return this;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
