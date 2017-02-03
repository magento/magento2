/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return {

        $weightSwitcher: $('[data-role=weight-switcher]'),
        $weight: $('#weight'),

        /**
         * Hide weight switcher
         */
        hideWeightSwitcher: function () {
            this.$weightSwitcher.hide();
        },

        /**
         * Is locked
         * @returns {*}
         */
        isLocked: function () {
            return this.$weight.is('[data-locked]');
        },

        /**
         * Disabled
         */
        disabled: function () {
            this.$weight.addClass('ignore-validate').prop('disabled', true);
        },

        /**
         * Enabled
         */
        enabled: function () {
            this.$weight.removeClass('ignore-validate').prop('disabled', false);
        },

        /**
         * Switch Weight
         * @returns {*}
         */
        switchWeight: function () {
            return this.productHasWeight() ? this.enabled() : this.disabled();
        },

        /**
         * Product has weight
         * @returns {Bool}
         */
        productHasWeight: function () {
            return $('input:checked', this.$weightSwitcher).val() === '1';
        },

        /**
         * Notify product weight is changed
         * @returns {*|jQuery}
         */
        notifyProductWeightIsChanged: function () {
            return $('input:checked', this.$weightSwitcher).trigger('change');
        },

        /**
         * Change
         * @param {String} data
         */
        change: function (data) {
            var value = data !== undefined ? +data : !this.productHasWeight();

            $('input[value=' + value + ']', this.$weightSwitcher).prop('checked', true);
        },

        /**
         * Constructor component
         */
        'Magento_Catalog/js/product/weight-handler': function () {
            this.bindAll();
            this.switchWeight();
        },

        /**
         * Bind all
         */
        bindAll: function () {
            this.$weightSwitcher.find('input').on('change', this.switchWeight.bind(this));
        }
    };
});
