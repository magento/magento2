/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [],
    function() {
        "use strict";
        var cache = [];
        return {
            get: function(addressKey) {
                if (cache[addressKey]) {
                    return cache[addressKey];
                }
                return false;
            },
            set: function(addressKey, data) {
                cache[addressKey] = data;
            }
        };
    }
);
