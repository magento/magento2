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
define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "Magento_DesignEditor/js/dialog"
], function($){

    /**
     * Theme quick edit controls
     */
    $.widget('vde.themeControl', {
        options: {
            themeData: null,
            saveEventName: 'quickEditSave',     //@TODO is it used at all?
            isActive: false
        },

        /**
         * Bind widget events
         * @protected
         */
        _init: function() {
            this.options._textControl.on('click.editThemeTitle', $.proxy(this._onEdit, this));
            this.options._editThemeNameControl.on('click.toggleEditThemeTitle', $.proxy(function() {
                if (this.options.isActive) {
                    this._cancelEdit();
                } else {
                    this._onEdit();
                }
            }, this));
            this.options._saveTitleBtn.on('click.submitForm', $.proxy(function() {
                this.options._formControl.trigger('submit');
                return false;
            }, this));
            this.options._formControl.on('submit.saveThemeTitle', $.proxy(function() {
                this._onSave();
                return false;
            }, this));
            this.document
                .on('click.cancelEditThemeTitle', $.proxy(this._onCancel, this))
                .on('keyup', $.proxy(function(e) {
                    //ESC button
                    if (e.keyCode === 27) {
                        this._cancelEdit();
                    }
                }, this));
        },

        /**
         * Widget initialization
         * @protected
         */
        _create: function() {
            this.options._textControl = this.widget().find('.theme-assigned-data > .theme-title');
            this.options._editThemeNameControl = this.widget().find('.edit-theme-title');
            this.options._inputControl = this.widget().find('.edit-theme-title-form');
            this.options._formControl = this.widget().find('.edit-theme-title-form');
            this.options._saveTitleBtn = this.widget().find('.action-save');
            this.options._control = this.widget().find('.theme-control-title');

            this.options.themeData = this.widget().data('widget-options');
        },

        /**
         * Edit event
         * @protected
         */
        _onEdit: function() {
            if (this.options.isActive) {
                return;
            }
            this.options.isActive = true;
            this.options._textControl.hide();
            this.options._inputControl.show().focus();
            this._setThemeTitle(this.options.themeData.theme_title);
        },

        /**
         * Save changed theme data
         * @protected
         */
        _onSave: function() {
            if(!this.options.isActive) {
                return;
            }
            var params = {
                theme_id: this.options.themeData.theme_id,
                theme_title: this._getThemeTitle()
            };
            $('#messages').html('');
            $.ajax({
                url: this.options.url,
                type: 'POST',
                dataType: 'json',
                data: params,
                showLoader: true,
                success: $.proxy(function(response) {
                    if (response.success) {
                        this.options.themeData.theme_title = this._getThemeTitle();
                        this._setThemeTitle(this.options.themeData.theme_title);
                    }
                    this._cancelEdit();
                }, this),
                error: $.proxy(function() {
                    this._cancelEdit();
                    alert($.mage.__('Sorry, there was an unknown error.'));
                }, this)
            });
        },

        /**
         * Get the entered value
         * @return {string}
         * @protected
         */
        _getThemeTitle: function() {
            return this.options._inputControl.find('input').val();
        },

        /**
         * Set the saved value
         * @param title {string}
         * @return {*}
         * @protected
         */
        _setThemeTitle: function(title) {
            this.options._textControl
                .text(title)
                .attr('title', title);
            this.options._inputControl.find('input').val(title);
            return this;
        },

        /**
         * Cancel saving theme title
         * @param event {*}
         * @protected
         */
        _onCancel: function(event) {
            if (this.options.isActive && this.widget().has($(event.target)).length === 0) {
                this._cancelEdit();
            }
        },

        /**
         * Cancel editing mode
         * @protected
         */
        _cancelEdit: function() {
            this.options.isActive = false;
            this.options._textControl.show();
            this.options._inputControl.hide();
        }
    });

    $( document ).ready(function( ) {
        var body = $('body');
        body.on('loaded', function() {
            body.trigger('contentUpdated');
        });
        $('.action-duplicate').on('click', function() {
            $('body').loadingPopup({
                timeout: false
            });
        });
    });


});