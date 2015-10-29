/**
 * Copyright © 2015 Magento. All rights reserved.
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
        isLocked: function () {
            return this.$weight.is('[data-locked]');
        },
        disabled: function () {
            this.$weight.addClass('ignore-validate').prop('disabled', true);
        },
        enabled: function () {
            this.$weight.removeClass('ignore-validate').prop('disabled', false);
        },
        switchWeight: function() {
            return this.productHasWeight() ? this.enabled() : this.disabled();
        },
        productHasWeight: function () {
            return $('input:checked', this.$weightSwitcher).val() == 1;
        },
        notifyProductWeightIsChanged: function () {
            return $('input:checked', this.$weightSwitcher).trigger('change');
        },
        change: function (data) {
            var value = data !== undefined ? +data : !this.productHasWeight();
            $('input[value='+ value +']', this.$weightSwitcher).prop('checked', true);
        },
        'Magento_Catalog/js/product/weight-handler': function () {
            this.bindAll();
            this.switchWeight();
        },
        bindAll: function () {
            this.$weightSwitcher.find('input').on('change', this.switchWeight.bind(this));
        }
    };
});
