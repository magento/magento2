/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            loadPlayer: 'Magento_ProductVideo/js/load-player',
            fotoramaVideoEvents: 'Magento_ProductVideo/js/fotorama-add-video-events',
            vimeoAPI: 'https://secure-a.vimeocdn.com/js/froogaloop2.min.js'
        }
    },
    shim: {
        vimeoAPI: {}
    }
};
