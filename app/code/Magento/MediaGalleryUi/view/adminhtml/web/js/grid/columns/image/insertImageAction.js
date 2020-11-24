/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global FORM_KEY, tinyMceEditors */
define([
    'jquery',
    'wysiwygAdapter',
    'underscore',
    'mage/translate'
], function ($, wysiwyg, _, $t) {
    'use strict';

    return {

        /**
         * Insert provided image in wysiwyg if enabled, or widget
         *
         * @param {Object} record
         * @param {Object} config
         * @returns {Boolean}
         */
        insertImage: function (record, config) {
            var targetElement;

            if (record === null) {
                return false;
            }
            targetElement = this.getTargetElement(window.MediabrowserUtility.targetElementId);

            if (!targetElement.length) {
                window.MediabrowserUtility.closeDialog();
                throw $t('Target element not found for content update');
            }

            $.ajax({
                url: config.onInsertUrl,
                data: {
                    filename: record['encoded_id'],
                    'store_id': config.storeId,
                    'as_is': targetElement.is('textarea') ? 1 : 0,
                    'force_static_path': targetElement.data('force_static_path') ? 1 : 0,
                    'form_key': FORM_KEY
                },
                context: this,
                showLoader: true
            }).done($.proxy(function (data) {
                if (targetElement.is('textarea')) {
                    this.insertAtCursor(targetElement.get(0), data.content);
                    targetElement.focus();
                    $(targetElement).change();
                } else {
                    targetElement.val(data.content)
                        .data('size', data.size)
                        .data('mime-type', data.type)
                        .trigger('change');
                }
            }, this));
            window.MediabrowserUtility.closeDialog();
            targetElement.focus();
        },

        /**
         * Insert image to target instance.
         *
         * @param {Object} element
         * @param {*} value
         */
        insertAtCursor: function (element, value) {
            var sel, startPos, endPos, scrollTop;

            if ('selection' in document) {
                //For browsers like Internet Explorer
                element.focus();
                sel = document.selection.createRange();
                sel.text = value;
                element.focus();
            } else if (element.selectionStart || element.selectionStart == '0') { //eslint-disable-line eqeqeq
                //For browsers like Firefox and Webkit based
                startPos = element.selectionStart;
                endPos = element.selectionEnd;
                scrollTop = element.scrollTop;
                element.value = element.value.substring(0, startPos) + value +
                    element.value.substring(startPos, endPos) + element.value.substring(endPos, element.value.length);
                element.focus();
                element.selectionStart = startPos + value.length;
                element.selectionEnd = startPos + value.length + element.value.substring(startPos, endPos).length;
                element.scrollTop = scrollTop;
            } else {
                element.value += value;
                element.focus();
            }
        },

        /**
         * Return opener Window object if it exists, not closed and editor is active
         *
         * @param {String} targetElementId
         * return {Object|null}
         */
        getMediaBrowserOpener: function (targetElementId) {
            if (!_.isUndefined(wysiwyg) && wysiwyg.get(targetElementId) && !_.isUndefined(tinyMceEditors) &&
                !tinyMceEditors.get(targetElementId).getMediaBrowserOpener().closed
            ) {
                return tinyMceEditors.get(targetElementId).getMediaBrowserOpener();
            }

            return null;
        },

        /**
         * Get target element
         *
         * @param {String} targetElementId
         * @returns {*|n.fn.init|jQuery|HTMLElement}
         */
        getTargetElement: function (targetElementId) {
            var opener;

            if (!_.isUndefined(wysiwyg) && wysiwyg.get(targetElementId)) {
                opener = this.getMediaBrowserOpener(targetElementId) || window;
                targetElementId = tinyMceEditors.get(targetElementId).getMediaBrowserTargetElementId();

                return $(opener.document.getElementById(targetElementId));
            }

            return $('#' + targetElementId);
        }
    };
});
