/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'jquery/ui'
], function ($, $t) {
    'use strict';

    $.widget('mage.remainingCharacters', {
        options: {
            remainingText: $t('remaining'),
            tooManyText: $t('too many'),
            errorClass: 'mage-error',
            noDisplayClass: 'no-display'
        },

        /**
         * Initializes custom option component
         *
         * @private
         */
        _create: function () {
            this.note = $(this.options.noteSelector);
            this.counter = $(this.options.counterSelector);

            this.updateCharacterCount();
            this.element.on('change keyup paste', this.updateCharacterCount.bind(this));
        },

        /**
         * Updates counter message
         */
        updateCharacterCount: function () {
            var length = this.element.val().length,
                diff = this.options.maxLength - length;

            this.counter.text(this._formatMessage(diff));
            this.counter.toggleClass(this.options.noDisplayClass, length === 0);
            this.note.toggleClass(this.options.errorClass, diff < 0);
        },

        /**
         * Format remaining characters message
         *
         * @param {int} diff
         * @returns {String}
         * @private
         */
        _formatMessage: function (diff) {
            var count = Math.abs(diff),
                qualifier = diff < 0 ? this.options.tooManyText : this.options.remainingText;

            return '(' + count + ' ' + qualifier + ')';
        }
    });

    return $.mage.remainingCharacters;
});
