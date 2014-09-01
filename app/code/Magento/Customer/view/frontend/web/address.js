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
/*jshint browser:true, jquery:true*/
/*global confirm:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate"
], function($){
    "use strict";
    
    $.widget('mage.address', {
        /**
         * Options common to all instances of this widget.
         * @type {Object}
         */
        options: {
            deleteConfirmMessage: $.mage.__('Are you sure you want to delete this address?')
        },

        /**
         * Bind event handlers for adding and deleting addresses.
         * @private
         */
        _create: function() {
            $(document).on('click', this.options.addAddress, $.proxy(this._addAddress, this));
            $(document).on('click', this.options.deleteAddress, $.proxy(this._deleteAddress, this));
        },

        /**
         * Add a new address.
         * @private
         */
        _addAddress: function() {
            window.location = this.options.addAddressLocation;
        },

        /**
         * Delete the address whose id is specified in a data attribute after confirmation from the user.
         * @private
         * @param {Event}
         * @return {Boolean}
         */
        _deleteAddress: function(e) {
            if (confirm(this.options.deleteConfirmMessage)) {
                if (typeof $(e.target).parent().data('address') !== 'undefined') {
                    window.location = this.options.deleteUrlPrefix + $(e.target).parent().data('address');
                }
                else {
                    window.location = this.options.deleteUrlPrefix + $(e.target).data('address');
                }
            }
            return false;
        }
    });
});