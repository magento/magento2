/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

// require.s.contexts._ is an undocumented RequireJS internal with no public API
// alternative. Captured here to chain any previously registered handler so that
// sri.js does not silently overwrite it.
var existingOnNodeCreated = require.s &&
    require.s.contexts &&
    require.s.contexts._ &&
    require.s.contexts._.config &&
    require.s.contexts._.config.onNodeCreated;

require.config({
    onNodeCreated: function (node, config, moduleName, url) {
        'use strict';
        if (typeof existingOnNodeCreated === 'function') {
            existingOnNodeCreated.apply(this, arguments);
        }
        if ('sriHashes' in window && url in window.sriHashes) {
            node.setAttribute('integrity', window.sriHashes[url]);
            node.setAttribute('crossorigin', 'anonymous');
        }
    }
});
