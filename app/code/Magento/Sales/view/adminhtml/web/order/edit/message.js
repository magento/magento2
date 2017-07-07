/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('mage.orderEditDialog', {
        options: {
            url:     null,
            message: null,
            modal:  null
        },

        /**
         * @protected
         */
        _create: function () {
            this._prepareDialog();
        },

        /**
         * Show modal
         */
        showDialog: function () {
            this.options.dialog.html(this.options.message).modal('openModal');
        },

        /**
         * Redirect to edit page
         */
        redirect: function () {
            window.location = this.options.url;
        },

        /**
         * Prepare modal
         * @protected
         */
        _prepareDialog: function () {
            var self = this;

            this.options.dialog = $('<div class="ui-dialog-content ui-widget-content"></div>').modal({
                type: 'popup',
                modalClass: 'edit-order-popup',
                title: $.mage.__('Edit Order'),
                buttons: [{
                    text: $.mage.__('Ok'),
                    'class': 'action-primary',

                    /** @inheritdoc */
                    click: function () {
                        self.redirect();
                    }
                }]
            });
        }
    });

    return $.mage.orderEditDialog;
});
