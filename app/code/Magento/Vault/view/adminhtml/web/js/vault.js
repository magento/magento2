/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define([
    'jquery',
    'uiComponent'
], function ($, Class) {
    'use strict';

    return Class.extend({
        defaults: {
            $selector: null,
            selector: 'edit_form',
            fieldset: '',
            active: false,
            imports: {
                onActiveChange: 'active'
            }
        },

        /**
         * Set list of observable attributes
         * @returns {exports.initObservable}
         */
        initObservable: function () {
            var self = this;

            self.$selector = $('#' + self.selector);
            this._super()
                .observe(['active']);

            // re-init payment method events
            self.$selector.off('changePaymentMethod.' + this.getCode())
                .on('changePaymentMethod.' + this.getCode(), this.changePaymentMethod.bind(this));

            if (this.active()) {
                $('#' + this.fieldset + ' input:radio:first').trigger('click');
            }

            return this;
        },

        /**
         * Enable/disable current payment method
         * @param {Object} event
         * @param {String} method
         * @returns {exports.changePaymentMethod}
         */
        changePaymentMethod: function (event, method) {
            this.active(method === this.getCode());

            return this;
        },

        /**
         * Triggered when payment changed
         * @param {Boolean} isActive
         */
        onActiveChange: function (isActive) {
            if (!isActive) {
                this.$selector.trigger('setVaultNotActive.' + this.getCode());

                return;
            }

            $('#' + this.fieldset + ' input:radio:first').trigger('click');
            window.order.addExcludedPaymentMethod(this.getCode());
        },

        /**
         * Get payment method code
         * @returns {String}
         */
        getCode: function () {
            return this.code;
        }
    });
});
