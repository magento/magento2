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
 * @category    checkout multi-shipping addresses
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
    $.widget('mage.multiShipping', {
        options: {
            addNewAddressBtn: 'button[data-role="add-new-address"]', // Add a new multishipping address.
            addNewAddressFlag: '#add_new_address_flag', // Hidden input field with value 0 or 1.
            canContinueBtn: 'button[data-role="can-continue"]', // Continue (update quantity or go to shipping).
            canContinueFlag: '#can_continue_flag' // Hidden input field with value 0 or 1.
        },

        /**
         * Bind event handlers to click events for corresponding buttons.
         * @private
         */
        _create: function() {
            $(this.options.addNewAddressBtn).on('click', $.proxy(this._addNewAddress, this));
            $(this.options.canContinueBtn).on('click', $.proxy(this._canContinue, this));
        },

        /**
         * Add a new address. Set the hidden input field and submit the form. Then enter a new shipping address.
         * @private
         */
        _addNewAddress: function() {
            $(this.options.addNewAddressFlag).val(1);
            this.element.submit();
        },

        /**
         * Can the user continue to the next step? The data-flag attribute holds either 0 (no) or 1 (yes).
         * @private
         * @param event {Event} - Click event on the corresponding button.
         */
        _canContinue: function(event) {
            $(this.options.canContinueFlag).val(parseInt($(event.currentTarget).data('flag'), 10));
        }
    });

});