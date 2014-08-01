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
    "mage/translate"
], function($){
    'use strict';

    $.widget('vde.customCssPanel', {
        options: {
            saveCustomCssUrl: null,
            downloadCustomCssUrl: null,
            customCssCode: '#custom_code',
            btnUpdateCss: '[data-action="update"]',
            btnDeleteCss: '[data-action="delete"]',
            btnUpdateDownload: '[data-action="download"]',
            fileRowInfo: '[data-file="uploaded-css"]'
        },

        updateButtons: function() {
            this._prepareUpdateButton();
        },

        _create: function() {
            this.btnCssUpdate = this.element.find(this.options.btnUpdateCss);
            this.btnCssDelete = this.element.find(this.options.btnDeleteCss);
            this.customCssCode = this.element.find(this.options.customCssCode);
            this.btnUpdateDownload = this.element.find(this.options.btnUpdateDownload);
            this.fileRowInfo = this.element.find(this.options.fileRowInfo);
            this._prepareUpdateButton();
            this.btnCssUpdate.prop('disabled', true);
            this._events();
        },

        _events: function() {
            this.btnCssUpdate.on('click', $.proxy(this._updateCustomCss, this));
            this.btnCssDelete.on('click', $.proxy(this._deleteCustomCss, this));
            this.customCssCode.on('input onchange change', $.proxy(this._editCustomCss, this));
            this.btnUpdateDownload.on('click', $.proxy(this._downloadCustomCss, this));
        },

        _editCustomCss: function()
        {
            this.btnCssUpdate.removeProp('disabled');
        },

        _downloadCustomCss: function() {
            $.mage.redirect(this.options.downloadCustomCssUrl);
        },

        _postUpdatedCustomCssContent: function()
        {
            this.btnCssUpdate.prop('disabled', true);
            $.ajax({
                type: 'POST',
                url:  this.options.saveCustomCssUrl,
                data: {custom_css_content: $(this.customCssCode).val()},
                dataType: 'json',
                success: $.proxy(function(response) {
                    this.element.trigger('addMessage', {
                        containerId: '#vde-tab-custom-messages-placeholder',
                        message: response.message
                    });
                    this.element.trigger('refreshIframe');
                    $('#custom-file-name').html(response.filename);
                    this._prepareUpdateButton();
                }, this),
                error: function() {
                    alert($.mage.__('Sorry, there was an unknown error.'));
                }
            });
            $('.vde-tools-content').trigger('resize.vdeToolsResize');
        },

        _updateCustomCss: function()
        {
            this._postUpdatedCustomCssContent();
        },

        _deleteCustomCss: function()
        {
            this.customCssCode.val('');
            this._postUpdatedCustomCssContent();
        },

        _prepareUpdateButton: function()
        {
            if (!$.trim($(this.customCssCode).val())) {
                this.fileRowInfo.addClass('no-display');
            } else {
                this.btnCssUpdate.prop('disabled', false);
                this.btnUpdateDownload.add(this.btnCssDelete).fadeIn();
                this.fileRowInfo.removeClass('no-display');
            }
        }
    });

});