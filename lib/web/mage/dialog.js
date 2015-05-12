/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "mage/template",
    "jquery/ui"
], function($, template){
    "use strict";

    /**
     * Dialog Widget - this widget is a wrapper for the jQuery UI Dialog
     */
    $.widget('mage.dialog', {
        options: {
            type: 'modal',
            title: null,
            template: '<div class="dialog-header"><div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span id="ui-id-3" class="ui-dialog-title"></span><a class="ui-dialog-titlebar-close ui-corner-all" role="button"><span class="ui-icon ui-icon-closethick">close</span></a></div></div><div class="dialog-content"></div><div class="dialog-actions"></div>',
            buttons: [],
            show: null,
            events: [],
            className: '',
            position: {
                modal: {
                    width: '75%',
                    position: 'fixed',
                    top: '50px',
                    left: '12.5%',
                    right: '12.5%'
                },
                slideOut: {
                    width: 'auto',
                    position: 'fixed',
                    top: '0',
                    left: '100%',
                    bottom: '0',
                    right: '0'
                }
            }
        },


        _create: function() {
            this._createWrapper();
            this._createTitlebar();
            this._createButtons();
            this._style();
            this._insertContent();

            this.element.on('open', _.bind(this.open, this));
            this.element.on('close', _.bind(this.close, this));

            return this.element;
        },
        open: function() {
            this._isOpen = true;

            this._position();
            this._createOverlay();
            this.uiDialog.show();
            this.uiDialog.addClass('ui-dialog-active');
            if ( this.options.type === 'slideOut' ) {
                this.uiDialog.animate({
                    left: '148px'
                }, 300);
            }
        },
        close: function() {
            var that = this;
            this._isOpen = false;

            if ( this.options.type === 'slideOut' ) {
                this.uiDialog.animate({
                    left: '100%'
                }, 300, function() {
                    that._destroyOverlay();
                });
            } else {
                this.uiDialog.removeClass('ui-dialog-active');
                this._destroyOverlay();
            }
        },
        _createWrapper: function() {
            this.uiDialog = $('<div/>')
                .addClass('ui-dialog ' + this.options.className)
                .hide()
                .html(this.options.template)
                .appendTo( this._appendTo() );
        },
        _appendTo: function() {
            var element = this.options.appendTo;

            if ( element && (element.jquery || element.nodeType) ) {
                return $( element );
            }

            return this.document.find( element || "body" ).eq( 0 );
        },
        _createTitlebar: function() {
            this.uiDialog.find('.ui-dialog-title').html(this.options.title);
            this.closeButton = this.uiDialog.find('.ui-dialog-titlebar-close');
            this.closeButton.on('click', _.bind(this.close, this));
        },
        _createButtons: function() {
            var that = this;

            this.buttonsPane = this.uiDialog.find('.dialog-actions');
             _.each(this.options.buttons, function(btn){
                var button = $('<button type="button"></button>')
                    .addClass(btn.class).html(btn.text);

                button.on('click', btn.click);
                that.buttonsPane.append(button);
            });
        },
        _createOverlay: function() {
            var that = this;

            document.body.style.overflow = 'hidden';
            this.overlay = $("<div>")
                .addClass("overlay_magento")
                .appendTo( this._appendTo() );
            this.overlay.on('click', function(){
                that.close();
            });
        },
        _insertContent: function() {
            this.content = this.uiDialog.find('.dialog-content');
            this.element
                .show()
                .appendTo( this.content );
        },
        _destroyOverlay: function() {
            document.body.style.overflow = 'auto';
            if ( this.overlay ) {
                this.overlay.remove();
                this.overlay = null;
            }
        },
        _style: function() {
            this.uiDialog.css({
                padding: '30px',
                backgroundColor: '#fff',
                zIndex: 1000
            });
        },
        _position: function() {
            var type = this.options.type;

            this.uiDialog.css(this.options.position[type]);
        }
    });

    return $.mage.dialog;
});