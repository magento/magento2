/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    $.widget('mage.giftMessage', {
        options: {
            rowPrefix: '#order-item-row-', // Selector prefix for item's row in the table.
            linkPrefix: '#order-item-gift-message-link-', // Selector prefix for the 'Gift Message' link.
            duration: 100, // Toggle duration.
            expandedClass: 'expanded', // Class added/removed to/from the 'Gift Message' link.
            lastClass: 'last' // Class added/removed to/from the last item's row in the products table.
        },

        /**
         * Bind a click handler on the widget's element to toggle the gift message.
         * @private
         */
        _create: function() {
            this.element.on('click', $.proxy(this._toggleGiftMessage, this));
        },

        /**
         * Toggle the display of the item's corresponding gift message.
         * @private
         * @param event - {Object} - Click event.
         */
        _toggleGiftMessage: function(event) {
            var element = $(event.target), // Click target. The 'Gift Message' link or 'Close' button.
                options = this.options, // Cached widget options.
                itemId = element.data('item-id'), // The individual item's numeric id.
                link = $(options.linkPrefix + itemId), // The 'Gift Message' expandable link.
                row = $(options.rowPrefix + itemId), // The item's row in the products table.
                region = $('#' + element.attr('aria-controls')); // The gift message container region.
            region.toggle(options.duration, function() {
                if (region.attr('aria-expanded') === "true") {
                    region.attr('aria-expanded', "false");
                    if (region.hasClass(options.lastClass)) {
                        row.addClass(options.lastClass);
                    }
                } else {
                    region.attr('aria-expanded', "true");
                    if (region.hasClass(options.lastClass)) {
                        row.removeClass(options.lastClass);
                    }
                }
                link.toggleClass(options.expandedClass);
            });
            event.preventDefault(); // Prevent event propagation and avoid going to the link's href.
        }
    });

});