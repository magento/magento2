/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @deprecated since version 2.2.0
 */
;(function(window, $) {
    "use strict";
        $["localStorage"] = {
            get : function( key ) {
                if (window["localStorage"] !== null) {
                    return localStorage.getItem(key);
                }
                else {
                    return $.cookie(key);
                }
            },

            set: function( key, value ) {
                if (window["localStorage"] !== null) {
                    localStorage.setItem(key, value);
                }
                else {
                    $.cookie(key, value);
                }
            }
        };
})(window, jQuery);
