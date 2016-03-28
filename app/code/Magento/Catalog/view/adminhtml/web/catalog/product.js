/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require([
    'jquery'
], function ($) {
    'use strict';

    window.Product = {};

    function byId(id) {
        return $('#' + id);
    }

    function toogleFieldEditMode(toogleIdentifier, fieldId) {
        if ($(toogleIdentifier).is(':checked')) {
            enableFieldEditMode(fieldId);
        } else {
            disableFieldEditMode(fieldId);
        }
    }

    function disableFieldEditMode(fieldId) {
        byId(fieldId).prop('disabled', true);

        if (byId(fieldId + '_hidden').length) {
            byId(fieldId + '_hidden').prop('disabled', true);
        }
    }

    function enableFieldEditMode(fieldId) {
        byId(fieldId).prop('disabled', false);

        if (byId(fieldId + '_hidden').length) {
            byId(fieldId + '_hidden').prop('disabled', false);
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
        var hidden = urlKey.siblings('input[type=hidden]');
        var chbx = urlKey.siblings('input[type=checkbox]');
        var oldValue = chbx.val();

        chbx.prop('disabled', oldValue === urlKey.val());
        hidden.prop('disabled', chbx.prop('disabled'));
    }

    function onCustomUseParentChanged(element) {
        element = $(element);
        var useParent = element.val() == 1,
            parent = element.offsetParent().parent();

        parent.find('input, select, textarea').each(function (i, el) {
            el = $(el);
            if (element.prop('id') != el.prop('id')) {
                el.prop('disabled', useParent);
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

    $(onCompleteDisableInited);
});
