/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

(function ($, window) {
    "use strict";

    function wrapMethod(object, property, method, copyProperties) {
        if (!object) {
            throw new TypeError("Should wrap property of object");
        }

        if (typeof method != "function") {
            throw new TypeError("Method wrapper should be function");
        }

        var wrappedMethod = object[property],
            error;

        if ($.type(wrappedMethod) !== 'function') {
            error = new TypeError("Attempted to wrap " + (typeof wrappedMethod) + " property " +
                property + " as function");
        }

        if (wrappedMethod.restore) {
            error = new TypeError("Attempted to wrap " + property + " which is already wrapped");
        }

        if (error) {
            if (wrappedMethod._stack) {
                error.stack += '\n--------------\n' + wrappedMethod._stack;
            }
            throw error;
        }

        // IE 8 does not support hasOwnProperty.
        var owned = object.hasOwnProperty ?
            object.hasOwnProperty(property) :
            Object.prototype.hasOwnProperty.call(object, property);

        object[property] = method;
        method.displayName = property;
        // Stack trace which can be used to find what line of code the original method was created on.
        method._stack = (new Error('Stack Trace for original')).stack;

        method.restore = function () {
            if (!owned) {
                delete object[property];
            }
            if (object[property] === method) {
                object[property] = wrappedMethod;
            }
        };

        if (copyProperties) {
            for (var prop in wrappedMethod) {
                if (!Object.prototype.hasOwnProperty.call(method, prop)) {
                    method[prop] = wrappedMethod[prop];
                }
            }
        }

        return method;
    }

    function stub(object, property, func, copyProperties) {
        if (!!func && typeof func != "function") {
            throw new TypeError("Custom stub should be function");
        }

        var wrapper;

        if (func) {
            wrapper = func;
        } else {
            wrapper = stub.create();
        }

        if (!object && typeof property === "undefined") {
            return stub.create();
        }

        if (typeof property === "undefined" && typeof object == "object") {
            for (var prop in object) {
                if (typeof object[prop] === "function") {
                    stub(object, prop);
                }
            }

            return object;
        }

        return wrapMethod(object, property, wrapper, copyProperties);
    }

    $.extend(stub, (function () {
        var proto = {
            create: function create() {
                var functionStub = function () {
                    functionStub.callCount = functionStub.callCount ? functionStub.callCount + 1 : 1;
                    functionStub.lastCallArgs = arguments;
                    functionStub.callArgsStack.push(arguments);
                    if (functionStub.returnCallback && $.type(functionStub.returnCallback) === 'function') {
                        return functionStub.returnCallback.apply(functionStub.returnCallback, arguments);
                    } else if (functionStub.returnValue) {
                        return functionStub.returnValue;
                    }
                };
                $.extend(functionStub, stub);
                functionStub.reset();
                functionStub.displayName = "stub";
                return functionStub;
            },

            reset: function() {
                this.callCount = null;
                this.lastCallArgs = [];
                this.callArgsStack = [];
                this.returnValue = null;
                this.returnCallback = null;
            }
        };

        return proto;
    }()));

    window.jsunit = window.jsunit || {};
    $.extend(window.jsunit, {
        stub: stub
    });
})(jQuery, window);
