/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_ConfigurableProduct/js/variations/steps/summary',
    'jquery',
    'mage/translate'
], function (Summary, $) {
    'use strict';

    return Summary.extend({
        /** @inheritdoc */
        initObservable: function () {
            this._super();
            this.quantityFieldName = 'quantityPerSource';
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
