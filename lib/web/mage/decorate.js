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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            "jquery",
            "mage/translate"
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {

    var methods = {
        /**
         * Decorate a list (e.g. a <ul> containing <li>) recursively if specified.
         * @param {boolean} isRecursive
         */
        list: function (isRecursive) {
            return this.each(function() {
                var list = $(this);
                if (list.length > 0) {
                    var items = (typeof(isRecursive) === undefined || isRecursive) ?
                        list.find('li') :
                        list.children();
                    items.decorate('generic', ['odd', 'even', 'last']);
                }
            });
        },

        /**
         * Annotate a set of DOM elements with decorator classes.
         * @param {Array} decoratorParams
         */
        generic: function (decoratorParams) {
            var elements = $(this);
            if (elements) {
                var allSupportedParams = {
                    even: 'odd', // Flip jQuery odd/even so that index 0 is odd.
                    odd: 'even',
                    last: 'last',
                    first: 'first'
                };

                decoratorParams = decoratorParams || allSupportedParams;

                $.each(decoratorParams, function(index, param) {
                    if (param === 'even' || param === 'odd') {
                        elements.filter(':' + param).removeClass('odd even').addClass(allSupportedParams[param]);
                    } else {
                        elements.filter(':' + param).addClass(allSupportedParams[param]);
                    }
                });
            }
            return this;
        },

        /**
         * Decorate DOM elements in an HTML table with specified classes.
         * @param {Object} instanceOptions
         */
        table: function (instanceOptions) {
            return this.each(function() {
                var table = $(this);
                if (table.length > 0) {
                    var options = {
                        'tbody': false,
                        'tbody tr': ['odd', 'even', 'first', 'last'],
                        'thead tr': ['first', 'last'],
                        'tfoot tr': ['first', 'last'],
                        'tr td': ['last']
                    };

                    $.extend(options, instanceOptions || {});

                    $.each(options, function (key, value) {
                        if (options[key]) {
                            if (key === 'tr td') {
                                $.each(table.find('tr'), function () {
                                    $(this).find('td').decorate('generic', options['tr td']);
                                });
                            } else {
                                table.find(key).decorate('generic', value);
                            }
                        }
                    });
                }
            });
        },

        /**
         * Annotate data list elements with CSS classes.
         */
        dataList: function() {
            return this.each(function() {
                var list = $(this);
                if (list) {
                    list.find('dt').decorate('generic', ['odd', 'even', 'last']);
                    list.find('dd').decorate('generic', ['odd', 'even', 'last']);
                }
            });
        }
    };

    $.fn.decorate = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error($.mage.__('Method ' +  method + ' does not exist on jQuery.decorate'));
        }
    };

}));
