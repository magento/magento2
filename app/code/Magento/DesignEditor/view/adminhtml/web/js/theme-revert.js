/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "Magento_DesignEditor/js/dialog"
], function($){
    'use strict';
    
    /**
     * VDE revert theme button widget
     */
    $.widget('vde.vde-edit-button', $.ui.button, {
        options: {
            dialogSelector:  '#dialog-message-confirm-revert',
            dialog: undefined,
            eventData: {
                url: undefined,
                confirm: undefined
            }
        },

        /**
         * Element creation
         * @protected
         */
        _create: function() {
            this._bind();
            this._super();
        },

        /**
         * Bind handlers
         * @protected
         */
        _bind: function() {
            this.element.on('click.vde-edit-button',  $.proxy(this._onRevertEvent, this));
            $('body').on('refreshIframe', $.proxy(this._enableButton, this));
        },

        /**
         * Handler for 'revert-to-last' and 'revert-to-default' event
         * @private
         */
        _onRevertEvent: function() {
            if (this.element.hasClass('disabled')) {
                return false;
            }
            var dialog = this._getDialog();
            if (this.options.eventData.confirm && dialog) {
                this._showConfirmMessage(dialog, $.proxy(this._sendRevertRequest, this));
            } else {
                this._sendRevertRequest();
            }
            return false;
        },

        /**
         * Show confirmation message if it was assigned
         * @private
         */
        _showConfirmMessage: function(dialogElement, callback) {
            var dialog = dialogElement.data('dialog');
            var buttons = {
                text: $.mage.__('OK'),
                click: callback,
                'class': 'action-primary'
            };

            dialog.title.set(this.options.eventData.confirm.title);
            dialog.text.set(this.options.eventData.confirm.message);
            dialog.setButtons(buttons);
            dialog.open();
        },

        /**
         * Sent request to revert changes
         * @private
         */
        _sendRevertRequest: function() {
            $.ajax({
                url: this.options.eventData.url,
                type: 'GET',
                dataType: 'JSON',
                async: false,
                success: $.proxy(function(data) {
                    if (data.error) {
                        throw Error($.mage.__('We can\'t revert right now.'));
                        return;
                    }
                    document.location.reload();
                }, this),
                error: function() {
                    throw Error($.mage.__('We can\'t revert right now.'));
                }
            });
        },

        /**
         * Enable button
         * @private
         */
        _enableButton: function() {
            this.element.removeAttr('disabled');
            this.element.removeClass('disabled');
        },

        /**
         * Get dialog element
         * @returns {*|HTMLElement}
         * @private
         */
        _getDialog: function() {
            if (!this.options.dialog) {
                this.options.dialog = $(this.options.dialogSelector).dialog({
                    autoOpen:    false,
                    modal:       true,
                    width:       '75%',
                    dialogClass: 'vde-dialog',
                    open: function () {
                        $(this).closest('.ui-dialog').addClass('ui-dialog-active');
                    },
                    close: function () {
                        $(this).closest('.ui-dialog').removeClass('ui-dialog-active');
                    }
                });
            }
            return this.options.dialog;
        }
    });
    
    return $.vde['vde-edit-button'];
});