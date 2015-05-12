/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "mage/template",
    "text!ui/template/dialog/dialog.html",
    "jquery/ui"
], function($, template, dialogTemplate){
    "use strict";

    /**
     * Dialog Widget - this widget is a wrapper for the jQuery UI Dialog
     */
    $.widget('mage.dialog', {
        options: {
            type: 'modal',
            title: null,
            template: template(dialogTemplate),
            buttons: [],
            events: [],
            dialogClass: '',
            dialogActiveClass: 'ui-dialog-active',
            overlayClass: 'overlay_magento',
            dialogTitleSelector: '.ui-dialog-title',
            dialogCloseBtnSelector: '.ui-dialog-titlebar-close',
            dialogContentSelector: '.dialog-content',
            dialogActionsSelector: '.dialog-actions',
            appendTo: 'body',
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

            this.element.on('openDialog', _.bind(this.openDialog, this));
            this.element.on('closeDialog', _.bind(this.closeDialog, this));

            return this.element;
        },
        openDialog: function() {
            this._isOpen = true;

            this._position();
            this._createOverlay();
            this.uiDialog.show();
            this.uiDialog.addClass(this.options.dialogActiveClass);
            if ( this.options.type === 'slideOut' ) {
                this.uiDialog.animate({
                    left: '148px'
                }, 300);
            }
        },
        closeDialog: function() {
            var that = this;
            this._isOpen = false;

            if ( this.options.type === 'slideOut' ) {
                this.uiDialog.animate({
                    left: '100%'
                }, 300, function() {
                    that._destroyOverlay();
                });
            } else {
                this.uiDialog.removeClass(this.options.dialogActiveClass);
                this._destroyOverlay();
            }
        },
        _createWrapper: function() {
            this.uiDialog = $(this.options.template({data: this.options}))
                .addClass(this.options.dialogClass)
                .appendTo(this.options.appendTo);
        },
        _createTitlebar: function() {
            this.uiDialog.find(this.options.dialogTitleSelector).html(this.options.title);
            this.closeButton = this.uiDialog.find(this.options.dialogCloseBtnSelector);
            this.closeButton.on('click', _.bind(this.closeDialog, this));
        },
        _insertContent: function() {
            this.content = this.uiDialog.find(this.options.dialogContentSelector);
            this.element
                .show()
                .appendTo( this.content );
        },
        _createButtons: function() {
            var that = this;

            this.buttonsPane = this.uiDialog.find(this.options.dialogActionsSelector);
            _.each(this.options.buttons, function(btn, key) {
                var button = that.buttonsPane.children()[key];

                button.on('click', btn.click);
            });
        },
        _createOverlay: function() {
            var that = this;

            document.body.style.overflow = 'hidden';
            this.overlay = $('<div></div>')
                .addClass(this.options.overlayClass)
                .appendTo( this.options.appendTo );
            this.overlay.on('click', function(){
                that.closeDialog();
            });
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