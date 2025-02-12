/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/* @api */
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
            var self = this,
                paymentSelector = '[name="payment[method]"][value="' + this.getCode() + '"]:checked';

            self.$selector = $('#' + self.selector);
            this._super()
                .observe(['active']);

            if (self.$selector.find(paymentSelector).length !== 0) {
                this.active(true);
            }

            $('#' + self.fieldset).find('[name="payment[token_switcher]"]')
                .on('click', this.rememberTokenSwitcher.bind(this));

            // re-init payment method events
            self.$selector.off('changePaymentMethod.' + this.getCode())
                .on('changePaymentMethod.' + this.getCode(), this.changePaymentMethod.bind(this));

            if (this.active()) {
                this.chooseTokenSwitcher();
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
         * Save last chosen token switcher
         * @param {Object} event
         * @returns {exports.rememberTokenSwitcher}
         */
        rememberTokenSwitcher: function (event) {
            $('#' + this.selector).data('lastTokenSwitcherId', event.target.id);

            return this;
        },

        /**
         * Select token switcher
         * @returns {exports.chooseTokenSwitcher}
         */
        chooseTokenSwitcher: function () {
            var lastTokenSwitcherId = $('#' + this.selector).data('lastTokenSwitcherId');

            if (lastTokenSwitcherId) {
                $('#' + lastTokenSwitcherId).trigger('click');
            } else {
                $('#' + this.fieldset + ' input:radio:first').trigger('click');
            }

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
            this.chooseTokenSwitcher();
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
