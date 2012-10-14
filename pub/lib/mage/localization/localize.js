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
 * @category    localization
 * @package     mage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint eqnull:true browser:true jquery:true*/
/*global Globalize:true */
(function ($) {
    //closure localize object
    var localize = function (locale) {
        this.localize = Globalize;
        if (locale == null) {
            this.localize.culture('en');
        } else {
            this.localize.culture(locale);
        }
        this.dateFormat = ['d', 'D', 'f', 'F', 'M', 'S', 't', 'T', 'Y'];
        this.numberFormat = ['n', 'n1', 'n3', 'd', 'd2', 'd3', 'p', 'p1', 'p3', 'c', 'c0'];
    };
    localize.prototype.name = function () {
        return this.localize.culture().name;
    };
    localize.prototype.date = function (dateParam, format) {
        if ($.inArray(format.toString(), this.dateFormat) < 0) {
            return 'Invalid date formatter';
        }
        if (dateParam instanceof Date) {
            return this.localize.format(dateParam, format);
        }
        var d = new Date(dateParam.toString());
        if (d == null || d.toString === 'Invalid Date') {
            return d.toString;
        } else {
            return this.localize.format(d, format);
        }
    };
    localize.prototype.number = function (numberParam, format) {
        if ($.inArray(format.toString(), this.numberFormat)) {
            return 'Invalid number formatter';
        }
        if (typeof numberParam === 'number') {
            return this.localize.format(numberParam, format);
        }
        var num = Number(numberParam);
        if (num == null || isNaN(num)) {
            return numberParam;
        } else {
            return this.localize.format(num, format);
        }
    };
    localize.prototype.currency = function (currencyParam) {
        if (typeof currencyParam === 'number') {
            return this.localize.format(currencyParam, 'c');
        }
        var num = Number(currencyParam);
        if (num == null || isNaN(num)) {
            return currencyParam;
        } else {
            return this.localize.format(num, 'c');
        }
    };

    $.extend(true, $, {
        mage: {
            localize: function() {},
            locale: function (locale) {
                if (locale != null && locale.length > 0) {
                    $.mage.localize = new localize(locale);
                } else {
                    $.mage.localize = new localize();
                 }
            }
        }
    });

    $.mage.locale($.mage.language.code);
})(jQuery);


