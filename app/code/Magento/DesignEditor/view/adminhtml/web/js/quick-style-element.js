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
/*global alert*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate"
], function($){
    'use strict';

    $.widget('vde.quickStyleElement', {
        options: {
            changeEvent: 'change.quickStyleElement',
            focusEvent: 'focus.quickStyleElement',
            saveQuickStylesUrl: null,
            backgroundSelector: '.color-box',
            controlSelector: '.control'
        },

        _init: function() {
            this._bind();
        },

        _bind: function() {

            this.element.on(this.options.changeEvent, $.proxy(this._onChange, this))
                .on(this.options.focusEvent, $.proxy(this._onFocus, this));
        },

        _onFocus: function() {
            this.oldValue = this.element.val();
        },

        _onChange: function() {
            if (this.element.attr('type') === 'checkbox') {
                this.element.trigger('quickStyleElementBeforeChange');
            }

            if (this.oldValue !== this.element.val() || this.element.attr('type') === 'checkbox') {
                this._send();
            }
        },

        _setSwitchColor: function() {
            this.element.closest(this.options.controlSelector)
                .find(this.options.backgroundSelector)
                .css('background-color', this.element.val());
        },

        _send: function() {
            var data = {
                id: this.element.attr('id'),
                value: this.element.val()
            };

            $.ajax({
                type: 'POST',
                url: this.options.saveQuickStylesUrl,
                data: data,
                dataType: 'json',
                global: false,
                success: $.proxy(function(response) {
                    if (response.error) {
                        alert(response.message);
                    }
                    this._setSwitchColor();
                    this.element.trigger('refreshIframe');
                }, this),
                error: function() {
                    alert($.mage.__('Sorry, there was an unknown error.'));
                }
            });
        }
    });

});