/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     js
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint eqnull:true browser:true jquery:true*/
(function($) {
    $.extend(true, $, {
        mage: {
            cookies: (function() {
                this.set = function(name, value) {
                    var expires = arguments[2] || $.cookie.defaults.expires;
                    var path = arguments[3] || $.cookie.defaults.path;
                    var domain = arguments[4] || $.cookie.defaults.domain;
                    var secure = arguments[5] || $.cookie.defaults.secure;
                    document.cookie = name + "=" + encodeURIComponent(value) +
                        ((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
                        ((path == null) ? "" : ("; path=" + path)) +
                        ((domain == null) ? "" : ("; domain=" + domain)) +
                        ((secure === true) ? "; secure" : "");
                };
                this.get = function(name) {
                    var arg = name + "=";
                    var alen = arg.length;
                    var clen = document.cookie.length;
                    var i = 0;
                    var j = 0;
                    while (i < clen) {
                        j = i + alen;
                        if (document.cookie.substring(i, j) === arg) {
                            return $.mage.cookies.getCookieVal(j);
                        }
                        i = document.cookie.indexOf(" ", i) + 1;
                        if (i === 0) {
                            break;
                        }
                    }
                    return null;
                };
                this.clear = function(name) {
                    if($.mage.cookies.get(name)) {
                        $.mage.cookies.set(name, "", new Date("Jan 01 1970 00:00:01 GMT"));
                    }
                };
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
})(jQuery);
