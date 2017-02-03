/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Catalog/js/product/weight-handler'
], function ($, weight) {
    'use strict';

    return {
        $type: $('#product_type_id'),

        /**
         * Init
         */
        init: function () {

            if (weight.productHasWeight()) {
                this.type = {
                    virtual: 'virtual',
                    real: this.$type.val() //simple, configurable
                };
            } else {
                this.type = {
                    virtual: this.$type.val(), //downloadable, virtual, grouped, bundle
                    real: 'simple'
                };
            }
            this.type.current = this.$type.val();

            this.bindAll();
        },

        /**
         * Bind all
         */
        bindAll: function () {
            $(document).on('setTypeProduct', function (event, type) {
                this.setType(type);
            }.bind(this));

            //direct change type input
            this.$type.on('change', function () {
                this.type.current = this.$type.val();
                this._notifyType();
            }.bind(this));
        },

        /**
         * Set type
         * @param {String} type - type product (downloadable, simple, virtual ...)
         * @returns {*}
         */
        setType: function (type) {
            return this.$type.val(type || this.type.real).trigger('change');
        },

        /**
         * Notify type
         * @private
         */
        _notifyType: function () {
            $(document).trigger('changeTypeProduct', this.type);
        }
    };
});
