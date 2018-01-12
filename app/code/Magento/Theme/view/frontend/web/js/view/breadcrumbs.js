/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'Magento_Theme/js/model/breadcrumb-list',
    'jquery/ui'
], function ($, mageTemplate, breadcrumbList) {
    'use strict';

    /**
     * Breadcrumb Widget.
     */
    $.widget('mage.breadcrumbs', {
        options: {
            crumbs: [],
            insertContainer: 'ul.items',
            crumbTemplate: mageTemplate(
                '<li class="item <%- name %>">' +
                    '<% if (link) { %>' +
                    '<a href="<%= link %>" title="<%- title %>"><%- label %></a>' +
                    '<% } else if (last) { %>' +
                    '<strong><%- label %></strong>' +
                    '<% } else { %>' +
                    '<%- label %>' +
                    '<% } %>' +
                '</li>'
            )
        },

        /** @inheritdoc */
        _init: function () {
            this._super();
            this._render();
        },

        /**
         * Render breadcrumb.
         *
         * @private
         */
        _render: function () {
            var html = '',
                crumbs = breadcrumbList,
                crumb,
                index;

            this._decorate(crumbs);

            for (index in crumbs) { //eslint-disable-line guard-for-in
                crumb = crumbs[index];
                html += this._renderCrumb(crumb);
            }

            if (html.length) {
                $(this.element).find(this.options.insertContainer).html(html);
            }
        },

        /**
         * Render crumb.
         *
         * @param {Object} crumbInfo
         * @return {String}
         * @private
         */
        _renderCrumb: function (crumbInfo) {
            var crumbTpl = this.options.crumbTemplate,
                html;

            html = crumbTpl(crumbInfo);

            return html;
        },

        /**
         * Decorate list.
         *
         * @param {Array} list
         * @private
         */
        _decorate: function (list) {

            if (list.length) {
                list[0].first = true;
            }

            if (list.length > 1) {
                list[list.length - 1].last = true;
            }
        }
    });

    return $.mage.breadcrumbs;
});
