/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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