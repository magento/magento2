/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
    ],
    function() {
        "use strict";
        var rendererList = [];
        return {
            registerRenderer: function(renderer) {
                rendererList.push(renderer);
            },
            getRenderer: function () {
                return rendererList;
            }
        }
    }
);
