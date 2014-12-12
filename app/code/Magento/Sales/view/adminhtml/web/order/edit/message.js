/**
 * @category    Mage
 * @package     Magento_Sales
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    "jquery",
    "jquery/ui",
    "mage/translate"
], function($){
    "use strict";

    $.widget('mage.orderEditDialog', {
        options: {
            url:     null,
            message: null,
            dialog:  null
        },

        /**
         * @protected
         */
        _create: function () {
            this._prepareDialog();
        },

        /**
         * Show dialog
         */
        showDialog: function() {
            this.options.dialog.html(this.options.message).dialog('open');
        },

        /**
         * Redirect to edit page
         */
        redirect: function() {
            window.location = this.options.url;
        },

        /**
         * Prepare dialog
         * @protected
         */
        _prepareDialog: function() {
            var self = this;

            this.options.dialog = $('<div class="ui-dialog-content ui-widget-content"></div>')
                .dialog({
                    autoOpen:    false,
                    title:       $.mage.__('Edit Order'),
                    modal:       true,
                    resizable:   false,
                    width:       500,
                    buttons: [{
                        text: $.mage.__('Ok'),
                        click: function(){
                            self.redirect();
                        }
                    }, {
                        text: $.mage.__('Cancel'),
                        click: function(){
                            $(this).dialog('close');
                        }
                    }]
                });
        }
    });
    
    return $.mage.orderEditDialog;
});