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

});
