/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint eqnull:true browser:true jquery:true*/
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            "jquery",
            "mage/mage"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    $.extend(true, $, {
        mage: {
            cookies: (function() {
                /**
                 * Cookie default values.
                 * @type {Object}
                 */
                this.defaults = {
                    expires: null,
                    path: '/',
                    domain: null,
                    secure: false
                };

                /**
                 * Calculate cookie expiration date based on its lifetime.
                 * @param {Object} options Cookie option values
                 * @return {(null|Date)} Calculated cookie expiration date or null if no lifetime provided.
                 * @private
                 */
                this._lifetimeToExpires = function(options) {
                    if (options.lifetime && (options.lifetime > 0)) {
                        var expires = options.expires || new Date();
                        return new Date(expires.getTime() + options.lifetime * 1000);
                    }
                    return null;
                };

                /**
                 * Set a cookie's value by cookie name based on optional cookie options.
                 * @param {string} name The name of the cookie.
                 * @param {string} value The cookie's value.
                 * @param {Object} options Optional options (e.g. lifetime, expires, path, etc.)
                 */
                this.set = function(name, value, options) {
                    options = $.extend({}, this.defaults, options || {});
                    var expires = this._lifetimeToExpires(options) || options.expires;
                    var path = options.path;
                    var domain = options.domain;
                    var secure = options.secure;
                    document.cookie = name + "=" + encodeURIComponent(value) +
                        ((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
                        ((path == null) ? "" : ("; path=" + path)) +
                        ((domain == null) ? "" : ("; domain=" + domain)) +
                        ((secure === true) ? "; secure" : "");
                };

                /**
                 * Get a cookie's value by cookie name.
                 * @param {string} name The name of the cookie.
                 * @return {(null|string)}
                 */
                this.get = function(name) {
                    var arg = name + "=";
                    var alen = arg.length;
                    var clen = document.cookie.length;
                    var i = 0;
                    var j = 0;
                    while (i < clen) {
                        j = i + alen;
                        if (document.cookie.substring(i, j) === arg) {
                            return this.getCookieVal(j);
                        }
                        i = document.cookie.indexOf(" ", i) + 1;
                        if (i === 0) {
                            break;
                        }
                    }
                    return null;
                };

                /**
                 * Clear a cookie's value by name.
                 * @param {string} name The name of the cookie being cleared.
                 */
                this.clear = function(name) {
                    if (this.get(name)) {
                        this.set(name, "", {expires: new Date("Jan 01 1970 00:00:01 GMT")});
                    }
                };

                /**
                 * Return URI decoded cookie component value (e.g. expires, path, etc.) based on a
                 * numeric offset in the document's cookie value.
                 * @param {number} offset Offset into the document's cookie value.
                 * @return {string}
                 */
                this.getCookieVal = function(offset) {
                    var endstr = document.cookie.indexOf(";", offset);
                    if(endstr === -1){
                        endstr = document.cookie.length;
                    }
                    return decodeURIComponent(document.cookie.substring(offset, endstr));
                };
                return this;
            }())
        }
    });
}));