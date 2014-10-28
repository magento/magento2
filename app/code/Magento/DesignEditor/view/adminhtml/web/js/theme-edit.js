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
    "mage/translate",
    "Magento_DesignEditor/js/dialog"
], function($){

    'use strict';
    /**
     * Widget theme edit
     */
    $.widget('vde.themeEdit', {
        options: {
            editEvent: 'themeEdit',
            dialogSelector: '',
            confirmMessage: '',
            title: '',
            launchUrl: ''
        },
        themeId: null,

        /**
         * Form creation
         * @protected
         */
        _create: function() {
            this._bind();
        },

        /**
         * Bind handlers
         * @protected
         */
        _bind: function() {
            $('body').on(this.options.editEvent, $.proxy(this._onEdit, this));
        },

        /**
         * @param event
         * @param data
         * @protected
         */
        _onEdit: function(event, data) {
            this.themeId = data.theme_id;
            var dialog = data.dialog = $(this.options.dialogSelector).data('dialog');
            dialog.messages.clear();
            dialog.text.set(this.options.confirmMessage);
            dialog.title.set(this.options.title);
            var buttons = (data.confirm && data.confirm.buttons) || [{
                text: $.mage.__('OK'),
                'class': 'primary',
                click: $.proxy(this._reloadPage, this)
            }];

            dialog.setButtons(buttons);
            dialog.open();
        },

        /**
         * @param event
         * @protected
         */
        _reloadPage: function(event) {
            event.preventDefault();
            event.returnValue = false;
            var childWindow = window.open([this.options.launchUrl + 'theme_id', this.themeId].join('/'));
            if ($.browser.msie) {
                $(childWindow.document).ready($.proxy(this._doReload, this, childWindow));
            } else {
                $(childWindow).load($.proxy(this._doReload, this, childWindow));
            }

        },

        /**
         * @param childWindow
         * @private
         */
        _doReload: function(childWindow) {
            if (childWindow.document.readyState === "complete") {
                window.location.reload();
            } else {
                setTimeout($.proxy(this._doReload, this, childWindow), 1000);
            }
        }
    });

});