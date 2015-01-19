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
     * Widget theme edit
     */
    $.widget('vde.themeEdit', {
        options: {
            editEvent: 'themeEdit',
            dialogSelector: '',
            confirmMessage: '',
            title: '',
            launchUrl: ''
        },
        themeId: null,

        /**
         * Form creation
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
            $('body').on(this.options.editEvent, $.proxy(this._onEdit, this));
        },

        /**
         * @param event
         * @param data
         * @protected
         */
        _onEdit: function(event, data) {
            this.themeId = data.theme_id;
            var dialog = data.dialog = $(this.options.dialogSelector).data('dialog');
            dialog.messages.clear();
            dialog.text.set(this.options.confirmMessage);
            dialog.title.set(this.options.title);
            var buttons = (data.confirm && data.confirm.buttons) || [{
                text: $.mage.__('OK'),
                'class': 'primary',
                click: $.proxy(this._reloadPage, this)
            }];

            dialog.setButtons(buttons);
            dialog.open();
        },

        /**
         * @param event
         * @protected
         */
        _reloadPage: function(event) {
            event.preventDefault();
            event.returnValue = false;
            
            var childWindow = window.open([this.options.launchUrl + 'theme_id', this.themeId].join('/'));

            $(childWindow).load($.proxy(this._doReload, this, childWindow));
        },

        /**
         * @param childWindow
         * @private
         */
        _doReload: function(childWindow) {
            if (childWindow.document.readyState === "complete") {
                window.location.reload();
            } else {
                setTimeout($.proxy(this._doReload, this, childWindow), 1000);
            }
        }
    });
    
    return $.vde.themeEdit;
});