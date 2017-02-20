/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable strict */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'mage/translate'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    var methods = {
        /**
         * Decorate a list (e.g. a <ul> containing <li>) recursively if specified.
         * @param {Boolean} isRecursive
         */
        list: function (isRecursive) {
            return this.each(function () {
                var list = $(this),
                    items;

                if (list.length > 0) {
                    items = typeof isRecursive === undefined || isRecursive ?
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
            var elements = $(this),
                allSupportedParams;

            if (elements) {
                allSupportedParams = {
                    even: 'odd', // Flip jQuery odd/even so that index 0 is odd.
                    odd: 'even',
                    last: 'last',
                    first: 'first'
                };

                decoratorParams = decoratorParams || allSupportedParams;

                $.each(decoratorParams, function (index, param) {
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
            return this.each(function () {
                var table = $(this),
                    options;

                if (table.length > 0) {
                    options = {
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
        dataList: function () {
            return this.each(function () {
                var list = $(this);

                if (list) {
                    list.find('dt').decorate('generic', ['odd', 'even', 'last']);
                    list.find('dd').decorate('generic', ['odd', 'even', 'last']);
                }
            });
        }
    };

    /**
     * @param {String} method
     * @return {*}
     */
    $.fn.decorate = function (method) {
        var message;

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        }

        message = $.mage.__('Method %s does not exist on jQuery.decorate');
        $.error(message.replace('%s', method));
    };
}));
