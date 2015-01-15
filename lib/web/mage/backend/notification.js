/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true browser:true*/
define([
    "jquery",
    "jquery/ui",
    "jquery/template"
], function($){
    "use strict";
    
    $.widget("mage.notification", {
        options: {
            templates: {
                global: '<div class="messages"><div class="message {{if error}}error{{/if}}"><div>${message}</div></div></div>'
            }
        },

        /**
         * Notification creation
         * @protected
         */
        _create: function() {
            $.each(this.options.templates, function(key, template) {
                $.template(key + 'Notification', template);
            });
            $(document).on('ajaxComplete ajaxError', $.proxy(this._add, this));
        },

        /**
         * Add new message
         * @protected
         * @param {Object} event object
         * @param {Object} jqXHR The jQuery XMLHttpRequest object returned by $.ajax()
         * @param {Object}
         */
        _add: function(event, jqXHR) {
            try {
                var response = JSON.parse(jqXHR.responseText);
                if (response && response.error && response.html_message) {
                    $('#messages').html(response.html_message);
                }
            } catch(e) {}
        },

        /**
         * Adds new message.
         *
         * @param {Object} data - Data with a message to be displayed.
         */
        add: function(data){
            var message = $.tmpl(this.options.templates.global, data);

            $("#messages").append(message);

            return this;
        },

        /**
         * Removes error messages.
         */
        clear: function(){
            $('#messages').html('');
        }
    });
    
    return $.mage.notification;
});
