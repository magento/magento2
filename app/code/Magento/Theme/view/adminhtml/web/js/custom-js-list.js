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
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "jquery/template"
], function($){

    'use strict';

    $.widget('theme.themeJsList', {
        options : {
            templateId : null,
            emptyTemplateId : null,
            refreshFileListEvent : null,
            prefixItemId : '',
            suffixItemId : ''
        },

        /**
         * Initialize widget
         *
         * @protected
         */
        _create : function () {
            this._bind();
        },

        /**
         * Bind event handlers
         *
         * @protected
         */
        _bind : function () {
            $('body').on(this.options.refreshFileListEvent, $.proxy(this._onRefreshList, this));
        },

        /**
         *Render js files list
         *
         * @param event
         * @param data
         * @protected
         */
        _onRefreshList : function (event, data) {
            $(this.element).html('');
            if (data.jsList.length) {
                this._renderList(data.jsList);
            } else {
                this._renderEmptyList();
            }
        },

        /**
         *Get item js list id
         *
         * @param fileId
         * @return string
         * @protected
         */
        _getItemId : function (fileId) {
            return this.options.prefixItemId + fileId + this.options.suffixItemId;
        },

        /**
         * Render js list
         *
         * @param jsList
         * @protected
         */
        _renderList : function (jsList) {
            for (var index = 0; index < jsList.length; index++) {
                var itemTmpl = $("<li></li>").html($(this.options.templateId).html());
                $(itemTmpl).attr('class', ($(this.options.templateId).attr('class')));
                itemTmpl.attr('id', this._getItemId(jsList[index].id));
                itemTmpl.html(itemTmpl.tmpl(jsList[index]));
                itemTmpl.removeClass('no-display');
                itemTmpl.appendTo(this.element);
            }
        },

        /**
         * Set empty js list
         *
         * @protected
         */
        _renderEmptyList : function () {
            var itemTmpl = $("<li></li>").html($(this.options.emptyTemplateId).html());
            $(itemTmpl).attr('class', ($(this.options.emptyTemplateId).attr('class')));
            itemTmpl.attr('id', 'empty-js-list');
            itemTmpl.removeClass('no-display');
            itemTmpl.appendTo(this.element);
        }
    });


});