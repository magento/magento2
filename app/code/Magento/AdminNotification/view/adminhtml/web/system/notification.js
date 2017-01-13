/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'Magento_Ui/js/modal/modal'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.systemMessageDialog', $.mage.modal, {
        options: {
            modalClass: 'modal-system-messages',
            systemMessageTemplate:
                '<% _.each(data.items, function(item) { %>' +
                    '<li class="message message-warning <% if (item.severity == 1) { %>error<% } else { %>warning<% } %>">' +
                        '<%= item.text %>' +
                    '</li>' +
                '<% }); %>'
        },

        _create: function() {
            this.options.title = $('#message-system-all').attr('title');
            this._super();
        },

        openModal: function (severity) {
            var superMethod = $.proxy(this._super, this);
            //this.modal.options

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
        },
        closeModal: function () {
            this._super();
        }
    });

    $(document).ready(function () {
        $('#system_messages .message-system-short .error').on('click', function () {
            $('#message-system-all').systemMessageDialog('openModal', 1);
        });

        $('#system_messages .message-system-short .warning').on('click', function () {
            $('#message-system-all').systemMessageDialog('openModal', 2);
        });
    });

    return $.mage.systemMessageDialog;
});
