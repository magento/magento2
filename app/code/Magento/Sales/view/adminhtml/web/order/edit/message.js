/**
 * @category    Mage
 * @package     Magento_Sales
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui",
    'Magento_Ui/js/dialog/dialog',
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
            this.options.dialog.html(this.options.message).trigger('openDialog');
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
            this.options.dialog = $('<div class="ui-dialog-content ui-widget-content"></div>').dialog();
        }
    });

    return $.mage.orderEditDialog;
});
