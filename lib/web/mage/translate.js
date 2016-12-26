/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable strict */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'mage/mage'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    $.extend(true, $, {
        mage: {
            translate: (function () {
                /**
                 * Key-value translations storage
                 * @type {Object}
                 * @private
                 */
                var _data = {};

                /**
                 * Add new translation (two string parameters) or several translations (object)
                 */
                this.add = function () {
                    if (arguments.length > 1) {
                        _data[arguments[0]] = arguments[1];
                    } else if (typeof arguments[0] === 'object') {
                        $.extend(_data, arguments[0]);
                    }
                };

                /**
                 * Make a translation with parsing (to handle case when _data represents tuple)
                 * @param {String} text
                 * @return {String}
                 */
                this.translate = function (text) {
                    return _data[text] ? _data[text] : text;
                };

                return this;
            }())
        }
    });
    $.mage.__ = $.proxy($.mage.translate.translate, $.mage.translate);

    return $.mage.__;
}));
