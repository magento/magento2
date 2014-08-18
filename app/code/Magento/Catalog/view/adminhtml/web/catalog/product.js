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
define([
    "jquery",
    "jquery/ui",
    "prototype"
], function(jQuery){

window.Product = {};

(function ($) {
    $.widget("mage.productAttributes", {
        _create: function () {
            this._on({'click':'_showPopup'});
        },
        _prepareUrl: function() {
            var name = $('[data-role=product-attribute-search]').val();
            return this.options.url +
                (/\?/.test(this.options.url) ? '&' : '?') +
                'set=' + $('#attribute_set_id').val() +
                '&attribute[frontend_label]=' +
                window.encodeURIComponent(name);
        },
        _showPopup: function (event) {
            var wrapper = $('<div id="create_new_attribute"/>').appendTo('body').dialog({
                title: 'New Attribute',
                width: 600,
                minHeight: 650,
                modal: true,
                resizable: false,
                resizeStop: function(event, ui) {
                    iframe.height($(this).outerHeight() + 'px');
                    iframe.width($(this).outerWidth() + 'px');
                }
            });
            wrapper.trigger('processStart');
            var iframe = $('<iframe id="create_new_attribute_container">').attr({
                src: this._prepareUrl(event),
                frameborder: 0,
                style: "position:absolute;top:58px;left:0px;right:0px;bottom:0px"
            });
            iframe.on('load', function () {
                wrapper.trigger('processStop');
                $(this).css({
                    height:  wrapper.outerHeight() + 'px',
                    width: wrapper.outerWidth() + 'px'
                });
            });
            wrapper.append(iframe);

            wrapper.on('dialogclose', function () {
                var dialog = this;
                var doc = iframe.get(0).document;
                if (doc && $.isFunction(doc.execCommand)) {
                    //IE9 break script loading but not execution on iframe removing
                    doc.execCommand('stop');
                    iframe.remove();
                }
                $(dialog).remove();
            });
        }
    });
})(jQuery);

function toogleFieldEditMode(toogleIdentifier, fieldContainer) {
    if ($(toogleIdentifier).checked) {
        enableFieldEditMode(fieldContainer);
    } else {
        disableFieldEditMode(fieldContainer);
    }
}

function disableFieldEditMode(fieldContainer) {
    $(fieldContainer).disabled = true;
    if ($(fieldContainer + '_hidden')) {
        $(fieldContainer + '_hidden').disabled = true;
    }
}

function enableFieldEditMode(fieldContainer) {
    $(fieldContainer).disabled = false;
    if ($(fieldContainer + '_hidden')) {
        $(fieldContainer + '_hidden').disabled = false;
    }
}

function onCompleteDisableInited() {
    jQuery.each(jQuery('[data-disable]'), function() {
        var item = jQuery(this).data('disable');
        disableFieldEditMode(item);
    });
}

function onUrlkeyChanged(urlKey) {
    urlKey = $(urlKey);
    var hidden = urlKey.next('input[type=hidden]');
    var chbx = urlKey.next('input[type=checkbox]');
    var oldValue = chbx.value;
    chbx.disabled = (oldValue == urlKey.value);
    hidden.disabled = chbx.disabled;
}

function onCustomUseParentChanged(element) {
    var useParent = (element.value == 1) ? true : false;
    element.up(2).select('input', 'select', 'textarea').each(function(el){
        if (element.id != el.id) {
            el.disabled = useParent;
        }
    });
    element.up(2).select('img').each(function(el){
        if (useParent) {
            el.hide();
        } else {
            el.show();
        }
    });
}

window.onCustomUseParentChanged = onCustomUseParentChanged;
window.onUrlkeyChanged = onUrlkeyChanged;
window.onCompleteDisableInited = onCompleteDisableInited;
window.enableFieldEditMode = enableFieldEditMode;
window.disableFieldEditMode = disableFieldEditMode;
window.toogleFieldEditMode = toogleFieldEditMode;

Event.observe(window, 'load', onCompleteDisableInited);

});