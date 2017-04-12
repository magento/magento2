/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return {
        $type: $('#product_type_id'),

        /**
         * Init
         */
        init: function () {
            this.type = {
                init: this.$type.val(),
                current: this.$type.val()
            };

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
            return this.$type.val(type || this.type.init).trigger('change');
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
