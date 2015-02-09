/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require([
    "jquery",
    "prototype"
], function(jQuery){

    window.Product = {};

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
        jQuery.each(jQuery('[data-disable]'), function () {
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
        element.up(2).select('input', 'select', 'textarea').each(function (el) {
            if (element.id != el.id) {
                el.disabled = useParent;
            }
        });
        element.up(2).select('img').each(function (el) {
            if (useParent) {
                el.hide();
            } else {
                el.show();
            }
        });
    }

    window.onCustomUseParentChanged = onCustomUseParentChanged;
    window.onUrlkeyChanged = onUrlkeyChanged;
    window.toogleFieldEditMode = toogleFieldEditMode;

    Event.observe(window, 'load', onCompleteDisableInited);
});