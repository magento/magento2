/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
require([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    $(function () {
        const engineField = $('#catalog_search_engine'),
            commentContainer = $('#row_catalog_search_engine p');

        engineField.change(() => {
            const engineValue = engineField.val();
            let commentText = 'If not specified, Default Search Engine will be used.';

            if (['elasticsearch7', 'elasticsearch8'].includes(engineValue)) {
                commentText = 'This search engine option is no longer supported by Adobe. ' +
                    'It is recommended to use OpenSearch as a search engine instead.';
            }

            commentContainer.text(commentText);
        });
    });
});
