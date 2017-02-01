/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.giftMessage', {
        options: {
            rowPrefix: '#order-item-row-', // Selector prefix for item's row in the table.
            linkPrefix: '#order-item-gift-message-link-', // Selector prefix for the 'Gift Message' link.
            duration: 100, // Toggle duration.
            expandedClass: 'expanded', // Class added/removed to/from the 'Gift Message' link.
            expandedContentClass: 'expanded-content', // Class added/removed to/from the 'Gift Message' content.
            lastClass: 'last' // Class added/removed to/from the last item's row in the products table.
        },

        /**
         * Bind a click handler on the widget's element to toggle the gift message.
         * @private
         */
        _create: function () {
            this.element.on('click', $.proxy(this._toggleGiftMessage, this));
        },

        /**
         * Toggle the display of the item's corresponding gift message.
         * @private
         * @param {jQuery.Event} event - Click event.
         */
        _toggleGiftMessage: function (event) {
            var element = $(event.target), // Click target. The 'Gift Message' link or 'Close' button.
                options = this.options, // Cached widget options.
                itemId = element.data('item-id'), // The individual item's numeric id.
                link = $(options.linkPrefix + itemId), // The 'Gift Message' expandable link.
                row = $(options.rowPrefix + itemId), // The item's row in the products table.
                region = $('#' + element.attr('aria-controls')); // The gift message container region.

            region.toggleClass(options.expandedContentClass, options.duration, function () {
                if (region.attr('aria-expanded') === 'true') {
                    region.attr('aria-expanded', 'false');

                    if (region.hasClass(options.lastClass)) {
                        row.addClass(options.lastClass);
                    }
                } else {
                    region.attr('aria-expanded', 'true');

                    if (region.hasClass(options.lastClass)) {
                        row.removeClass(options.lastClass);
                    }
                }
                link.toggleClass(options.expandedClass);
            });
            event.preventDefault(); // Prevent event propagation and avoid going to the link's href.
        }
    });

    return $.mage.giftMessage;
});
