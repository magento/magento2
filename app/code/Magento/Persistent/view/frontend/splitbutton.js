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
 * @category    Magento
 * @package     Magento_Persistent
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/*jshint browser:true jquery:true*/
(function($, undefined) {
    "use strict";
    $.widget('mage.splitButton', {
        /**
         * options with default values
         */
        options: {
            splitButton: '.split-button',
            arrowButton: '.change',
            activeClass: 'active'
        },

        /**
         * Initialize split button events
         * @private
         */
        _create: function() {
            $(document).on('click', this.options.splitButton + ' > ' + this.options.arrowButton, $.proxy(this._toggleDropDown, this));
            $(document).on('click', $.proxy(this._hideDropDown, this));
        },

        /**
         * Toggle css class for the split button to hide or show drop down menu
         * Saves current state of the target. Closes all open drop downs and then
         * depending on the stored state the target drop down is toggled.
         * @private
         * @param {Object} e
         */
        _toggleDropDown: function(e) {
            var state = $(e.target).closest(this.options.splitButton).hasClass(this.options.activeClass);
            this._hideDropDown();
            if (state) {
                this._hideDropDown();
            } else {
                $(e.target).closest(this.options.splitButton).addClass(this.options.activeClass);
            }
            return false;
        },

        /**
         * Hide all the drop down menus when clicked any where on the page
         * @private
         */
        _hideDropDown: function() {
            $(document).find(this.options.splitButton).removeClass(this.options.activeClass);
        }
    });
})(jQuery);
