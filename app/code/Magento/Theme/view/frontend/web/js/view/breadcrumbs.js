/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'Magento_Theme/js/model/breadcrumb-list',
    'text!Magento_Theme/templates/breadcrumbs.html',
    'jquery/ui'
], function ($, mageTemplate, breadcrumbList, tpl) {
    'use strict';

    /**
     * Breadcrumb Widget.
     */
    $.widget('mage.breadcrumbs', {

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
            var html,
                crumbs = breadcrumbList,
                template = mageTemplate(tpl);

            this._decorate(crumbs);

            html = template({
                'breadcrumbs': crumbs
            });

            if (html.length) {
                $(this.element).html(html);
            }
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
