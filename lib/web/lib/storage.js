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
