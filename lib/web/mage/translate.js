/**
 * Copyright © 2015 Magento. All rights reserved.
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
    /**
     * Generate array with matches
     * @param {string} text
     * @return {Array}
     */
    var parse = function (text){
        var reg = /{{(.*?)}}{{(.*?)}}{{(.*?)}}{{(.*?)}}/,
        matches = text.replace('{{{','{{').replace('}}}','}}').match(reg);
        return matches || [];
        },

        /**
         * Function returns parsed translation (must be second in the record,
         * example "Price": "{{{Price_1}}{{Price_2}}{{Price_3}}{{theme}}}" - need "Price_2")
         * @param {string} text
         * @return {string}
         */
        getParsedTranslation = function (text){
            return parse(text)[2] || text;
        } ;

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
                 * Make a translation (this func expect _data to be dictionary)
                 * @param {string} text
                 * @return {text}
                 */
                this.dumbTranslate = function (text) {
                    return _data[text] ? _data[text] : [text];
                };

                /**
                 * Make a translation with parsing (to handle case when _data represents tuple)
                 * @param {string} text
                 * @return {string}
                 */
                this.translate = function (text) {
                    return _data[text] ? getParsedTranslation(_data[text]) : text;
                };

                /**
                 * Make a translation with parsing, returns all matches (to handle case when _data represents tuple)
                 * @param {string} text
                 * @return {Array}
                 */
                this.parsedTranslate = function (text) {
                    return _data[text] ? parse(_data[text]) : [text];
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
