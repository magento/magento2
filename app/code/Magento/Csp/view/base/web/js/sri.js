/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
require.config({
    onNodeCreated: function (node, config, moduleName, url) {
        'use strict';
        if ('sriHashes' in window && url in window.sriHashes) {
            node.setAttribute('integrity', window.sriHashes[url]);
            node.setAttribute('crossorigin', 'anonymous');
        }
    }
});
