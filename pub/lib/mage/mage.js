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
 * @category    mage
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint eqnull:true browser:true jquery:true*/
/*global head:true */
(function($) {

    var _syncQueue = [];
    var _asyncQueue = [];
    var _cssQueue = [];

    // Add add arr to queue make sure elements are unique
    function _addToQueue(files, queue) {
        for (var i = 0; i < files.length; i++) {
            if (typeof files[i] === 'string' && $.inArray(files[i], queue) === -1) {
                queue.push(files[i]);
            }
        }
    }

    function _unique(arr) {
        var uniqueArr = [];
        for (var i = arr.length; i--;) {
            var val = arr[i];
            if ($.inArray(val, uniqueArr) === -1) {
                uniqueArr.unshift(val);
            }
        }
        return uniqueArr;
    }

    function _asyncLoad() {
        var x, s, i;
        head.js.apply({}, _unique(_asyncQueue));
        x = document.getElementsByTagName('script')[0];
        for (i = 0; i < _cssQueue.length; i++) {
            s = document.createElement('link');
            s.type = 'text/css';
            s.rel = 'stylesheet';
            s.href = _cssQueue[i];
            x.parentNode.appendChild(s);
        }
    }

    function _loadScript() {
        if (_syncQueue.length === 0) {
            _asyncLoad();
            return;
        }
        _syncQueue = _unique(_syncQueue);
        _syncQueue.push(_asyncLoad);
        head.js.apply({}, _syncQueue);
    }

    $.extend(true, $, {
        mage: {
            language: {
                cookieKey: 'language',
                en: 'en',
                code: 'en'
            },

            event: (function() {
                this.trigger = function (customEvent, data) {
                    $(document).triggerHandler(customEvent.toString(), data);
                };
                this.observe = function (customEvent, func) {
                    $(document).on(customEvent.toString(), func);
                };
                this.removeObserver = function (customEvent, func) {
                    $(document).unbind(customEvent, func);
                };
                return this;
            }()),

            constant: {
                KEY_BACKSPACE: 8,
                KEY_TAB: 9,
                KEY_RETURN: 13,
                KEY_ESC: 27,
                KEY_LEFT: 37,
                KEY_UP: 38,
                KEY_RIGHT: 39,
                KEY_DOWN: 40,
                KEY_DELETE: 46,
                KEY_HOME: 36,
                KEY_END: 35,
                KEY_PAGEUP: 33,
                KEY_PAGEDOWN: 34
            },

            load: (function() {
                this.jsSync = function () {
                    _addToQueue(arguments, _syncQueue);
                    return _syncQueue.length;
                };
                this.js = function () {
                    _addToQueue(arguments, _asyncQueue);
                    return _asyncQueue.length;
                };
                this.css = function () {
                    _addToQueue(arguments, _cssQueue);
                    return _cssQueue.length;
                };
                this.language = function (lang, jsMapping) {
                    var language = $.mage.language.code = lang;
                    if (language != null && language !== $.mage.language.en) {
                        _addToQueue(jsMapping.localize, _syncQueue);
                    }
                    return _syncQueue.length;
                };
                return this;
            }())
        }
    });

    $(window).on('load', _loadScript);

})(jQuery);