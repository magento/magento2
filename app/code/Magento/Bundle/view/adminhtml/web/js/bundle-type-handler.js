/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true expr:true*/
define([
    'jquery',
    'Magento_Catalog/catalog/type-events',
    'Magento_Catalog/js/product/weight-handler'
], function ($, productType, weight) {
    'use strict';

    return {

        /**
         * Constructor component
         */
        'Magento_Bundle/js/bundle-type-handler': function () {
            this.bindAll();
            this._initType();
        },

        /**
         * Bind all
         */
        bindAll: function () {
            $(document).on('changeTypeProduct', this._initType.bind(this));
        },

        /**
         * Init type
         * @private
         */
        _initType: function () {
            if (
                productType.type.real === 'bundle' &&
                productType.type.current !== 'bundle' &&
                !weight.isLocked()
            ) {
                weight.switchWeight();
            }
        }
    };
});
