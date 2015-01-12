/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "jquery/template",
    "Magento_DesignEditor/js/dialog"
], function($){

    /**
     * VDE theme remove button widget
     */
    $.widget('vde.themeDelete', {
        options: {
            dialogSelector:  '#dialog-message-confirm',
            deleteThemeEvent: 'delete',
            eventData: {}
        },

        /**
         * Element creation
         * @protected
         */
        _create: function() {
            this._bind();
        },

        /**
         * Bind handlers
         * @protected
         */
        _bind: function() {
            this.element.on(this.options.deleteThemeEvent,  $.proxy(this._onThemeDeleteEvent, this));
        },

        /**
         * Handler for theme delete
         * @param event
         * @param eventData
         * @protected
         */
        _onThemeDeleteEvent: function(event, eventData) {
            this.options.eventData = eventData;

            var dialog = this._getDialog();
            if (this.options.eventData.confirm && this.options.eventData.confirm.message && dialog) {
                this._showConfirmMessage(dialog, $.proxy(this._sendThemeRemoveRequest, this));
            } else {
                this._sendThemeRemoveRequest();
            }
        },

        /**
         * Show confirmation message before theme delete
         * @protected
         */
        _showConfirmMessage: function(dialogElement, callback) {
            var dialog = dialogElement.data('dialog');
            var buttons = [
                {
                    text: $.mage.__('No'),
                    click: $.proxy(function() {
                        this.close();
                    }, dialog),
                    'class': 'action-close'
                },
                {
                    text: $.mage.__('Yes'),
                    click: callback,
                    'class': 'primary'
                }
            ];

            dialog.title.set(this.options.eventData.title);
            dialog.text.set(this.options.eventData.confirm.message);
            dialog.setButtons(buttons);
            dialog.open();
        },

        /**
         * Sent request to remove theme
         * @protected
         */
        _sendThemeRemoveRequest: function() {
            var dialog = this._getDialog().data('dialog');
            dialog.close();

            $('body').loadingPopup({
                timeout: false
            });

            window.location = this.options.eventData.url;
        },

        /**
         * Get dialog element
         *
         * @returns {*|HTMLElement}
         * @protected
         */
        _getDialog: function() {
            return $(this.options.dialogSelector);
        }
    });

});