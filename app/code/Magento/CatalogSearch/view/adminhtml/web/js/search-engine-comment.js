/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
require([
    'jquery',
    'domReady!',
    'mage/translate'
], function ($) {
    'use strict';

    $(function () {
        const engineField = $('#catalog_search_engine'),
            commentContainer = $('#row_catalog_search_engine p'),
            defaultText = $.mage.__('If not specified, Default Search Engine will be used.'),
            unsupportedText = $.mage.__('This search engine option is no longer supported by Adobe. ' +
                'It is recommended to use OpenSearch as a search engine instead.'),
            updateCommentText = () => {
                const engineValue = engineField.val(),
                    newCommentText = ['elasticsearch8'].includes(engineValue) ?
                        unsupportedText : defaultText;

                if (commentContainer.text() !== newCommentText) {
                    commentContainer.text(newCommentText);
                }
            };

        engineField.change(updateCommentText);
        updateCommentText();
    });
});
