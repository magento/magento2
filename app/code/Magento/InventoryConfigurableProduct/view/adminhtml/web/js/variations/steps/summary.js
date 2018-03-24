/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_ConfigurableProduct/js/variations/steps/summary',
    'jquery',
    'underscore',
    'mage/translate'
], function (Summary, $, _) {
    'use strict';
    return Summary.extend({
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
    });
});
