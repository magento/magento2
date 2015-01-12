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

    $.template(
        'systemMessageDialog',
        '<li class="{{if severity == 1}}error{{else}}warning{{/if}}">{{html text}}</li>'
    );

    $.widget('mage.systemMessageDialog', $.ui.dialog, {
        options: {
            systemMessageTemplate: 'systemMessageDialog'
        },
        open: function(severity) {
            var superMethod = $.proxy(this._super, this);
            $.ajax({
                url: this.options.ajaxUrl,
                type: 'GET',
                data: {severity: severity}
            }).done($.proxy(function(data) {
                this.element.html(
                    $('<ul />', {'class': "message-system-list"}).append(
                        $.tmpl(this.options.systemMessageTemplate, data)
                    )
                ).trigger('contentUpdated');
                superMethod();
            }, this));
            return this;
        }
    });

    $(document).ready(function(){
        $('#system_messages .message-system-short .error').on('click', function() {
            $('#message-system-all').systemMessageDialog('open', 1);
        });
        $('#system_messages .message-system-short .warning').on('click', function() {
            $('#message-system-all').systemMessageDialog('open', 2);
        });
    });

    return $.mage.systemMessageDialog;
});
