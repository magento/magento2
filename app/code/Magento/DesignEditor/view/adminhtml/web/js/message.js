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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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