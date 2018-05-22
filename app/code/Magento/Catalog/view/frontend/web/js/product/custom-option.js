/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('mage.customOption', {
        options: {
            'remainingText': $.mage.__('remaining'),
            'tooManyText': $.mage.__('too many'),
            'errorClass': 'mage-error',
            'inputSelector': '.product-custom-option',
            'counterSelector': 'span.character-counter',
            'remainingCountSelector': 'span.count-remaining',
            'countMessageSelector': 'span.count-message'
        },

        /**
         * Initializes custom option component
         *
         * @private
         */
        _create: function () {
            this.input = this.element.find(this.options.inputSelector);
            this.counter = this.element.find(this.options.counterSelector);
            this.countRemaining = this.element.find(this.options.remainingCountSelector);
            this.countText = this.element.find(this.options.countMessageSelector);

            try {
                this.dataValidate = JSON.parse(this.input.attr('data-validate'));
            } catch (e) {
                this.dataValidate = {};
            }

            if (this.dataValidate.hasOwnProperty('maxlength')) {
                this.input.on('change keyup paste', function () {
                    this.updateCharacterCount();
                }.bind(this));
            }
        },

        /**
         * Updates counter message
         */
        updateCharacterCount: function () {
            var length = this.input.val().length,
                diff = this.dataValidate.maxlength - length;

            this.countRemaining.text(Math.abs(diff));
            this.countText.text(diff < 0 ? this.options.tooManyText : this.options.remainingText);
            this.counter.toggleClass('no-display', length === 0);

            if (diff < 0) {
                this.highlight();
            } else {
                this.unhighlight();
            }
        },

        /**
         * Highlights input and field note
         */
        highlight: function () {
            this.input.addClass(this.options.errorClass);
            this.element.find('.note').addClass(this.options.errorClass);
        },

        /**
         * Removes highlight from input and field note
         */
        unhighlight: function () {
            this.input.removeClass(this.options.errorClass);
            this.element.find('.note').removeClass(this.options.errorClass);
        }
    });

    return $.mage.customOption;
});
