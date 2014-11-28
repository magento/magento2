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
 * @category    mage file change/delete
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

    $.widget('mage.fileOption', {
        options: {
        },

        _create: function() {
            this.fileDeleteFlag = this.fileChangeFlag = false;
            this.inputField = this.element.find('input[name=' + this.options.fileName + ']')[0];
            this.inputFieldAction = this.element.find('input[name=' + this.options.fieldNameAction + ']')[0];
            this.fileNameSpan = this.element.parent('dd').find('.' + this.options.fileNamed);

            $(this.options.changeFileSelector).on('click', $.proxy(function() {
                this._toggleFileChange();
            }, this));
            $(this.options.deleteFileSelector).on('click', $.proxy(function() {
                this._toggleFileDelete();
            }, this));
        },

        /**
         * Toggles whether the current file is being changed or not. If the file is being deleted
         * then the option to change the file is disabled.
         * @private
         */
        _toggleFileChange: function() {
            this.element.toggle();
            this.fileChangeFlag = !this.fileChangeFlag;
            if (!this.fileDeleteFlag) {
                $(this.inputFieldAction).attr('value', this.fileChangeFlag ? 'save_new' : 'save_old');
                this.inputField.disabled = !this.fileChangeFlag;
            }
        },

        /**
         * Toggles whether the file is to be deleted. When the file is being deleted, the name of
         * the file is decorated with strike-through text and the option to change the file is
         * disabled.
         * @private
         */
        _toggleFileDelete: function() {
            this.fileDeleteFlag = $(this.options.deleteFileSelector + ':checked').val();
            $(this.inputFieldAction).attr('value',
                this.fileDeleteFlag ? '' : this.fileChangeFlag ? 'save_new' : 'save_old');
            this.inputField.disabled = this.fileDeleteFlag || !this.fileChangeFlag;
            this.fileNameSpan.css('text-decoration', this.fileDeleteFlag ? 'line-through' : 'none');
        }
    });
    
    return $.mage.fileOption;
});