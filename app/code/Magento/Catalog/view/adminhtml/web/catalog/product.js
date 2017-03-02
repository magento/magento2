/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require([
    'jquery'
], function ($) {
    'use strict';

    window.Product = {};

    /**
     * @param {String} id
     * @return {*|jQuery|HTMLElement}
     */
    function byId(id) {
        return $('#' + id);
    }

    /**
     * @param {String} fieldId
     */
    function disableFieldEditMode(fieldId) {
        var field = byId(fieldId);

        field.prop('disabled', true);

        if (field.next().hasClass('addafter')) {
            field.parent().addClass('_update-attributes-disabled');
        }

        if (byId(fieldId + '_hidden').length) {
            byId(fieldId + '_hidden').prop('disabled', true);
        }
    }

    /**
     * @param {String} fieldId
     */
    function enableFieldEditMode(fieldId) {
        var field = byId(fieldId);

        field.prop('disabled', false);

        if (field.parent().hasClass('_update-attributes-disabled')) {
            field.parent().removeClass('_update-attributes-disabled');
        }

        if (byId(fieldId + '_hidden').length) {
            byId(fieldId + '_hidden').prop('disabled', false);
        }
    }

    /**
     * @param {String} toogleIdentifier
     * @param {String} fieldId
     */
    function toogleFieldEditMode(toogleIdentifier, fieldId) {
        if ($(toogleIdentifier).is(':checked')) {
            enableFieldEditMode(fieldId);
        } else {
            disableFieldEditMode(fieldId);
        }
    }

    /**
     * On complete disable.
     */
    function onCompleteDisableInited() {
        var item;

        $.each($('[data-disable]'), function () {
            item = $(this).data('disable');
            disableFieldEditMode(item);
        });
    }

    /**
     * @param {String} urlKey
     */
    function onUrlkeyChanged(urlKey) {
        var hidden, chbx, oldValue;

        urlKey = byId(urlKey);
        hidden = urlKey.siblings('input[type=hidden]');
        chbx = urlKey.siblings('input[type=checkbox]');
        oldValue = chbx.val();

        chbx.prop('disabled', oldValue === urlKey.val());
        hidden.prop('disabled', chbx.prop('disabled'));
    }

    /**
     * @param {HTMLElement} element
     */
    function onCustomUseParentChanged(element) {
        var useParent, parent;

        element = $(element);
        useParent = element.val() == 1; //eslint-disable-line eqeqeq
        parent = element.offsetParent().parent();

        parent.find('input, select, textarea').each(function (i, el) {
            el = $(el);

            if (element.prop('id') !== el.prop('id')) {
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
