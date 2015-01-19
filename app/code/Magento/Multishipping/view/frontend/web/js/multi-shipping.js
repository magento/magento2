/**
 * @category    checkout multi-shipping addresses
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
    
    return $.mage.multiShipping;
});