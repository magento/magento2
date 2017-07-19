/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function ($, window) {
    "use strict";

    // List of define() calls with arguments and call stack
    var defineCalls = [];

    // Get current call stack, including script path information
    var getFileStack = function() {
        try {
            throw new Error();
        } catch (e) {
            if (!e.stack) {
                throw new Error('The browser needs to support Error.stack property');
            }
            return e.stack;
        }
    };

    // Intercept RequireJS define() calls, which are performed by AMD scripts upon loading
    window.define = function () {
        var stack = getFileStack();
        defineCalls.push({
            stack: stack,
            args: arguments
        });
    };

    window.require = function(dependencies, callback){
        return callback && callback();
    };

    // Exposed interface
    var requirejsUtil = {
        getDefineArgsInScript: function (scriptPath) {
            var result;
            for (var i = 0; i < defineCalls.length; i++) {
                if (defineCalls[i].stack.indexOf(scriptPath) >= 0) {
                    result = defineCalls[i].args;
                    break;
                }
            }
            return result;
        }
    };

    window.jsunit = window.jsunit || {};
    $.extend(window.jsunit, {requirejsUtil: requirejsUtil});
})(jQuery, window);
