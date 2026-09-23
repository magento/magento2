/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

/**
 * Shows a notice under "Merge JavaScript Files" when enabled (not recommended).
 */
define([
    'jquery',
    'domReady!',
    'mage/translate'
], function ($) {
    'use strict';

    var selectId = 'dev_js_merge_files',
        rowId = 'row_dev_js_merge_files',
        defaultText = $.mage.__('When disabled, each script is loaded separately.'),
        notRecommendedText = $.mage.__(
            'Enabling JavaScript Merging is not recommended. With HTTP/2 and later, browsers can request many files ' +
            'in parallel over one connection, so merging no longer provides any benefits.'
        );

    $(function () {
        var mergeField = $('#' + selectId),
            commentContainer = $('#' + rowId + ' p.note span');

        if (!mergeField.length || !commentContainer.length) {
            return;
        }

        function updateCommentText() {
            var value = mergeField.val(),
                newText = value === '1' ? notRecommendedText : defaultText;

            if (commentContainer.text() !== newText) {
                commentContainer.text(newText);
            }
        }

        mergeField.on('change', updateCommentText);
        updateCommentText();
    });
});
