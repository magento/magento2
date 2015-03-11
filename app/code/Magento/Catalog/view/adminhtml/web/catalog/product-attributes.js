/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('mage.productAttributes', {
        _create: function () {
            this._on({
                'click': '_showPopup'
            });
        },

        _prepareUrl: function () {
            var name = $('[data-role=product-attribute-search]').val();

            return this.options.url +
                (/\?/.test(this.options.url) ? '&' : '?') +
                'set=' + $('#attribute_set_id').val() +
                '&attribute[frontend_label]=' +
                window.encodeURIComponent(name);
        },

        _showPopup: function (event) {
            var wrapper,
                iframe;

            wrapper = $('<div id="create_new_attribute"/>').appendTo('body').dialog({
                // ToDo: refactor to a sliding panel
                title: 'New Attribute',
                width: '75%',
                minHeight: 650,
                modal: true,
                resizable: false,
                resizeStop: function () {
                    iframe.height($(this).outerHeight() + 'px');
                    iframe.width($(this).outerWidth() + 'px');
                },
                position: {
                    my: 'left top',
                    at: 'center top',
                    of: 'body'
                },
                open: function () {
                    $(this).closest('.ui-dialog').addClass('ui-dialog-active');

                    var topMargin = jQuery(this).closest('.ui-dialog').children('.ui-dialog-titlebar').outerHeight() + 45;
                    jQuery(this).closest('.ui-dialog').css('margin-top', topMargin);
                },
                close: function () {
                    $(this).closest('.ui-dialog').removeClass('ui-dialog-active');
                }
            });

            iframe = $('<iframe id="create_new_attribute_container">').attr({
                src: this._prepareUrl(event),
                frameborder: 0,
                style: 'position:absolute;top:58px;left:0px;right:0px;bottom:0px'
            });

            iframe.on('load', function () {
                $(this).css({
                    height: wrapper.outerHeight() + 'px',
                    width: wrapper.outerWidth() + 'px'
                });
            });

            wrapper.append(iframe);

            wrapper.on('dialogclose', function () {
                var dialog = this,
                    doc = iframe.get(0).document;

                if (doc && $.isFunction(doc.execCommand)) {
                    //IE9 break script loading but not execution on iframe removing
                    doc.execCommand('stop');
                    iframe.remove();
                }

                $(dialog).remove();
            });
        }
    });

    return $.mage.productAttributes;
});
