/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "jquery/template"
], function($){

    $.widget('vde.vdeMessage', {
        options: {
            addMessageEvent: 'addMessage',
            clearMessagesEvent: 'clearMessages',
            messageTemplateId: ''
        },

        /**
         * Initialize widget
         *
         * @protected
         */
        _create: function ()
        {
            this._bind();
        },

        /**
         * Bind events
         *
         * @protected
         */
        _bind: function()
        {
            var body = $('body');
            body.on(this.options.addMessageEvent, $.proxy(this._onAddMessage, this));
            body.on(this.options.clearMessagesEvent, $.proxy(this._onClearMessages, this));
        },

        /**
         * Handler for addMessage event
         *
         * @param event
         * @param data
         * @protected
         */
        _onAddMessage: function(event, data)
        {
            this._clearMessages(data.containerId);
            if (data.message) {
                this._addMessage(data.message, data.containerId);
            }
        },

        /**
         * Handler for clearMessages event
         *
         * @protected
         */
        _onClearMessages: function()
        {
            this._clearMessages(data.containerId);
        },

        /**
         * Delete all messages
         *
         * @param containerId
         * @protected
         */
        _clearMessages: function(containerId)
        {
            $(containerId).html('');
        },

        /**
         * Add message to container
         *
         * @param message
         * @param containerId
         * @private
         */
        _addMessage: function (message, containerId)
        {
            var messageTemplate = $($(this.options.messageTemplateId).clone());
            messageTemplate.removeAttr('id');
            messageTemplate.attr('class', ($(this.options.messageTemplateId).attr('class')));
            messageTemplate.html(messageTemplate.tmpl({message: message}));
            messageTemplate.removeClass('no-display');
            messageTemplate.appendTo(containerId);
        }
    });

});