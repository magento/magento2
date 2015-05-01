/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require([
    'jquery'
], function ($) {
    'use strict';

    window.Product = {};

    function byId(id) {
        return document.getElementById(id);
    }

    function toogleFieldEditMode(toogleIdentifier, fieldContainer) {
        if (byId(toogleIdentifier).checked) {
            enableFieldEditMode(fieldContainer);
        } else {
            disableFieldEditMode(fieldContainer);
        }
    }

    function disableFieldEditMode(fieldContainer) {
        byId(fieldContainer).disabled = true;

        if (byId(fieldContainer + '_hidden')) {
            byId(fieldContainer + '_hidden').disabled = true;
        }
    }

    function enableFieldEditMode(fieldContainer) {
        byId(fieldContainer).disabled = false;

        if (byId(fieldContainer + '_hidden')) {
            byId(fieldContainer + '_hidden').disabled = false;
        }
    }

    function onCompleteDisableInited() {
        $.each($('[data-disable]'), function () {
            var item = $(this).data('disable');
            disableFieldEditMode(item);
        });
    }

    function onUrlkeyChanged(urlKey) {
        urlKey = byId(urlKey);
        var hidden = $(urlKey).next('input[type=hidden]')[0];
        var chbx = $(urlKey).next('input[type=checkbox]')[0];
        var oldValue = chbx.value;

        chbx.disabled = (oldValue === urlKey.value);
        hidden.disabled = chbx.disabled;
    }

    function onCustomUseParentChanged(element) {
        var useParent = (element.value === 1) ? true : false,
            parent = $(element).parent().parent();

        parent.find('input, select, textarea').each(function (i, el) {
            if (element.id !== el.id) {
                el.disabled = useParent;
            }
        });

        parent.find('img').each(function (i, el) {
            if (useParent) {
                $(el).hide();
            } else {
                $(el).show();
            }
        });
    }

    window.onCustomUseParentChanged = onCustomUseParentChanged;
    window.onUrlkeyChanged = onUrlkeyChanged;
    window.toogleFieldEditMode = toogleFieldEditMode;

    $(window).load(onCompleteDisableInited);
});
