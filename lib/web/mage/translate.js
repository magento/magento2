/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
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
            translate: (function() {
                /**
                 * Key-value translations storage
                 * @type {Object}
                 * @private
                 */
                var _data = {};

                /**
                 * Add new translation (two string parameters) or several translations (object)
                 * @param {(Object.<string>|string)}
                 * @param {string}
                 */
                this.add = function() {
                    if (arguments.length > 1) {
                        _data[arguments[0]] = arguments[1];
                    } else if (typeof arguments[0] === 'object') {
                        $.extend(_data, arguments[0]);
                    }
                };

                /**
                 * Make a translation with parsing (to handle case when _data represents tuple)
                 * @param {string} text
                 * @return {string}
                 */
                this.translate = function (text) {
                    return _data[text] ? _data[text] : text;
                };

                return this;
            }())
        }
    });
    /**
     * Sort alias for jQuery.mage.translate.translate method
     * @type {function(string): string}
     */
    $.mage.__ = $.proxy($.mage.translate.translate, $.mage.translate);

    return $.mage.__;
}));
