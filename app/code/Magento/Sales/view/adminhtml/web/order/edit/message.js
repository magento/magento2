/**
 * @category    Mage
 * @package     Magento_Sales
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui",
    "mage/dialog",
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
            this.options.dialog.html(this.options.message).trigger('open');
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

            this.options.dialog = $('<div class="ui-dialog-content ui-widget-content"></div>').dialog({
                type: 'modal',
                className: 'edit-order-popup',
                title: $.mage.__('Edit Order'),
                buttons: [{
                    text: $.mage.__('Ok'),
                    'class': 'action-primary',
                    click: function(){
                        self.redirect();
                    }
                }, {
                    text: $.mage.__('Cancel'),
                    'class': 'action-close',
                    click: function(){
                        self.options.dialog.trigger('close');
                    }
                }]
            });
        }
    });

    return $.mage.orderEditDialog;
});
