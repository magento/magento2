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
 * @category    design
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
(function($) {
    'use strict';
    $.widget('vde.customCssPanel', {
        options: {
            saveCustomCssUrl: null,
            customCssCode: '#custom_code',
            btnUpdateCss: '#vde-tab-custom .action-update',
            btnUpdateDownload: '#vde-tab-custom .action-download'
        },

        updateButtons: function() {
            this._prepareUpdateButton();
        },

        _create: function() {
            this.btnCssUpdate = $(this.options.btnUpdateCss);
            this.customCssCode = $(this.options.customCssCode);
            this.btnUpdateDownload = $(this.options.btnUpdateDownload);
            this._prepareUpdateButton();
            this._events();
        },

        _events: function() {
            this.btnCssUpdate.on('click', $.proxy(this._updateCustomCss, this));
            this.customCssCode.on('input onchange', $.proxy(this._editCustomCss, this));
        },

        _editCustomCss: function()
        {
            if ($.trim($(this.customCssCode).val())) {
                this.btnCssUpdate.removeAttr('disabled');
            }
        },

        _updateCustomCss: function()
        {
            $.ajax({
                type: 'POST',
                url:  this.options.saveCustomCssUrl,
                data: {custom_css_content: $(this.customCssCode).val()},
                dataType: 'json',
                success: $.proxy(function(response) {
                    if (response.message_html) {
                        $('#vde-tab-custom-messages-placeholder').append(response.message_html);
                    }
                    this.element.trigger('refreshIframe');
                    this._prepareUpdateButton();
                }, this),
                error: function() {
                    alert($.mage.__('Error: unknown error.'));
                }
            });
        },

        _prepareUpdateButton: function()
        {
            if (!$.trim($(this.customCssCode).val())) {
                this.btnCssUpdate.attr('disabled', 'disabled');
                $(this.btnUpdateDownload).fadeOut();
            } else {
                $(this.btnUpdateDownload).fadeIn();
            }
        }
    });
})(window.jQuery);
