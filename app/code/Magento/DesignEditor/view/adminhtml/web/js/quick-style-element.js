/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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