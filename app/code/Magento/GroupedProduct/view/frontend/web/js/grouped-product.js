/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'jquery-ui-modules/widget',
    'jquery/jquery.parsequery'
], function ($) {
    'use strict';

    $.widget('mage.groupedProduct', {
        options: {
            qtySelector: 'input.qty',
            qtyNameSelector: 'super_group'
        },

        /**
         * Creates widget
         * @private
         */
        _create: function () {
            // Override defaults with URL query parameters and/or inputs values
            this._overrideDefaults();
        },

        /**
         * Override default options values settings with either URL query parameters or
         * initialized inputs values.
         * @private
         */
        _overrideDefaults: function () {
            var hashIndex = window.location.href.indexOf('#');

            if (hashIndex !== -1) {
                this._parseQueryParams(window.location.href.substr(hashIndex + 1));
            }
        },

        /**
         * Parse query parameters from a query string and set options values based on the
         * key value pairs of the parameters.
         * @param {*} queryString - URL query string containing query parameters.
         * @private
         */
        _parseQueryParams: function (queryString) {
            var queryParams = $.parseQuery({
                    query: queryString
                }),
                form = this.element,
                qtyNameSelector = this.options.qtyNameSelector,
                qtys = $(this.options.qtySelector, form);

            $.each(queryParams, $.proxy(function (key, value) {
                qtys.each(function (index, qty) {
                    var nameSelector = qtyNameSelector.concat('[', key, ']');

                    if (qty.name === nameSelector) {
                        $(qty).val(value);
                    }
                });
            }, this));
        }
    });

    return $.mage.groupedProduct;
});
