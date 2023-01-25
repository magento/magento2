/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery'
], function ($) {
    'use strict';

    return {

        /**
         * Get weight
         * @returns {*|jQuery|HTMLElement}
         */
        $weight: function () {
            return $('#weight');
        },

        /**
         * Weight Switcher
         * @returns {*|jQuery|HTMLElement}
         */
        $weightSwitcher: function () {
            return $('[data-role=weight-switcher]');
        },

        /**
         * Weight Change Toggle
         * @returns {*|jQuery|HTMLElement}
         */
        $weightChangeToggle: function () {
            return $('#toggle_weight');
        },

        /**
         * Is locked
         * @returns {*}
         */
        isLocked: function () {
            return this.$weight().is('[data-locked]');
        },

        /**
         * Disabled
         */
        disabled: function () {
            this.$weight().addClass('ignore-validate').prop('disabled', true);
        },

        /**
         * Enabled
         */
        enabled: function () {
            this.$weight().removeClass('ignore-validate').prop('disabled', false);
        },

        /**
         * Disabled Switcher
         */
        disabledSwitcher: function () {
            this.$weightSwitcher().find('input[type="radio"]').addClass('ignore-validate').prop('disabled', true);
        },

        /**
         * Enabled Switcher
         */
        enabledSwitcher: function () {
            this.$weightSwitcher().find('input[type="radio"]').removeClass('ignore-validate').prop('disabled', false);
        },

        /**
         * Switch Weight
         * @returns {*}
         */
        switchWeight: function () {
            if (this.hasWeightChangeToggle()) {
                return;
            }

            return this.productHasWeightBySwitcher() ? this.enabled() : this.disabled();
        },

        /**
         * Toggle Switcher
         */
        toggleSwitcher: function () {
            this.isWeightChanging() ? this.enabledSwitcher() : this.disabledSwitcher();
        },

        /**
         * Hide weight switcher
         */
        hideWeightSwitcher: function () {
            this.$weightSwitcher().hide();
        },

        /**
         * Has weight switcher
         * @returns {*}
         */
        hasWeightSwitcher: function () {
            return this.$weightSwitcher().is(':visible');
        },

        /**
         * Has weight
         * @returns {*}
         */
        hasWeight: function () {
            return this.$weight.is(':visible');
        },

        /**
         * Has weight change toggle
         * @returns {*}
         */
        hasWeightChangeToggle: function () {
            return this.$weightChangeToggle().is(':visible');
        },

        /**
         * Product has weight
         * @returns {Bool}
         */
        productHasWeightBySwitcher: function () {
            return $('input:checked', this.$weightSwitcher()).val() === '1';
        },

        /**
         * Product weight toggle is checked
         * @returns {Bool}
         */
        isWeightChanging: function () {
            return this.$weightChangeToggle().is(':checked');
        },

        /**
         * Change
         * @param {String} data
         */
        change: function (data) {
            var value = data !== undefined ? +data : !this.productHasWeightBySwitcher();

            $('input[value=' + value + ']', this.$weightSwitcher()).prop('checked', true);
            this.switchWeight();
        },

        /**
         * Constructor component
         */
        'Magento_Catalog/js/product/weight-handler': function () {
            this.bindAll();

            if (this.hasWeightSwitcher()) {
                this.switchWeight();
            }

            if (this.hasWeightChangeToggle()) {
                this.toggleSwitcher();
            }
        },

        /**
         * Bind all
         */
        bindAll: function () {
            this.$weightSwitcher().find('input').on('change', this.switchWeight.bind(this));
        }
    };
});
