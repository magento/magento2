/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
