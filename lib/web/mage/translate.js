/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/mage',
    'mageTranslationDictionary',
    'underscore'
], function ($, mage, dictionary, _) {
    'use strict';

    $.extend(true, $, {
        mage: {
            translate: (function () {
                /**
                 * Key-value translations storage
                 * @type {Object}
                 * @private
                 */
                var _data = dictionary;

                return {
                    /**
                     * Add new translation (two string parameters) or several translations (object)
                     */
                    add: function () {
                        if (arguments.length > 1) {
                            _data[arguments[0]] = arguments[1];
                        } else if (typeof arguments[0] === 'object') {
                            $.extend(_data, arguments[0]);
                        }
                    },

                    /**
                     * Make a translation with parsing (to handle case when _data represents tuple)
                     * @param {String} text
                     * @return {String}
                     */
                    translate: function (text) {
                        return typeof _data[text] !== 'undefined' ? _data[text] : text;
                    }
                };
            }())
        }
    });
    $.mage.__ = $.proxy($.mage.translate.translate, $.mage.translate);

    // Provide i18n wrapper to be used in underscore templates for translation
    _.extend(_, {
        /**
         * Make a translation using $.mage.__
         *
         * @param {String} text
         * @return {String}
         */
        i18n: function (text) {
            return $.mage.__(text);
        }
    });

    return $.mage.__;
});
