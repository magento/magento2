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
 * @package     mage.translate
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function($) {
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
                 * Make a translation
                 * @param {string} text
                 * @return {string}
                 */
                this.translate = function(text) {
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
})(jQuery);
