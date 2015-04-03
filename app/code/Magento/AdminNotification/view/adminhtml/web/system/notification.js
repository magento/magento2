/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/template',
    'jquery/ui'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.systemMessageDialog', $.ui.dialog, {
        options: {
            systemMessageTemplate:
                '<% _.each(data.items, function(item) { %>' +
                    '<li class="message message-warning <% if (item.severity == 1) { %>error<% } else { %>warning<% } %>">' +
                        '<%= item.text %>' +
                    '</li>' +
                '<% }); %>'
        },

        open: function (severity) {
            var superMethod = $.proxy(this._super, this);

            $.ajax({
                url: this.options.ajaxUrl,
                type: 'GET',
                data: {
                    severity: severity
                }
            }).done($.proxy(function (data) {
                var tmpl = mageTemplate(this.options.systemMessageTemplate, {
                    data: {
                        items: data
                    }
                });

                tmpl = $(tmpl);

                this.element.html(
                    $('<ul />', {
                        'class': 'message-system-list'
                    }).append(tmpl)
                ).trigger('contentUpdated');

                superMethod();
            }, this));

            return this;
        }
    });

    $(document).ready(function () {
        $('#system_messages .message-system-short .error').on('click', function () {
            $('#message-system-all').systemMessageDialog('open', 1);
        });

        $('#system_messages .message-system-short .warning').on('click', function () {
            $('#message-system-all').systemMessageDialog('open', 2);
        });
    });

    return $.mage.systemMessageDialog;
});
