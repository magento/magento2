/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "underscore",
    "mage/template",
    "text!ui/template/dialog/dialog.html",
    "jquery/ui",
    "mage/translate"
], function($, _,template, dialogTemplate){
    "use strict";

    /**
     * Dialog Widget - this widget is a wrapper for the jQuery UI Dialog
     */
    $.widget('mage.dialog', {
        options: {
            type: 'modal',
            title: '',
            template: dialogTemplate,
            buttons: [{
                text: $.mage.__('Ok'),
                class: 'action-primary',
                click: function(){
                    this.closeDialog();
                }
            }],
            events: [],
            dialogClass: '',
            dialogActiveClass: 'ui-dialog-active',
            overlayClass: 'overlay_magento',
            dialogBlock: '[data-role="dialog"]',
            dialogCloseBtn: '[data-role="closeBtn"]',
            dialogContent: '[data-role="content"]',
            dialogAction: '[data-role="action"]',
            appendTo: 'body',
            wrapperId: 'dialogs-wrapper',
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
                    left: '148px',
                    bottom: '0',
                    right: '0'
                }
            }
        },

        _create: function() {
            this.options.transitionEvent = this.whichTransitionEvent();
            this._createWrapper();
            this._renderDialog();
            this._createButtons();
            this._style();

            this.dialog.find(this.options.dialogCloseBtn).on('click',  _.bind(this.closeDialog, this));
            this.element.on('openDialog', _.bind(this.openDialog, this));
            this.element.on('closeDialog', _.bind(this.closeDialog, this));
        },
        _getElem: function(elem) {
            return this.dialog.find(elem);
        },
        openDialog: function() {
            this.options.isOpen = true;
            this._position();
            this._createOverlay();
            this.dialog.show();
            this.dialog.addClass(this.options.dialogActiveClass);

            return this.element;
        },
        closeDialog: function() {
            var that = this;

            this.options.isOpen = false;
            this.dialog.one(this.options.transitionEvent, function() {
                that._close();
            });
            this.dialog.removeClass(this.options.dialogActiveClass);
            if ( !this.options.transitionEvent ) {
                that._close();
            }

            return this.element;
        },
        _close: function() {
            this.dialog.hide();
            this._destroyOverlay();
            this._trigger('dialogClosed');
        },
        _createWrapper: function() {
            this.dialogWrapper = $('#'+this.options.wrapperId);
            if ( !this.dialogWrapper.length ) {
                this.dialogWrapper = $('<div></div>')
                     .attr('id', this.options.wrapperId)
                     .appendTo(this.options.appendTo);
            }
        },
        _renderDialog: function() {
            this.dialog = $(template(
                this.options.template,
                {
                    data: this.options
                })).appendTo(this.dialogWrapper);

            this.element.show().appendTo(this._getElem(this.options.dialogContent));
            this.dialog.hide();
        },
        _createButtons: function() {
            var that = this;

            this.buttons = this._getElem(this.options.dialogAction);
            _.each(this.options.buttons, function(btn, key) {
                var button = that.buttons[key];

                $(button).on('click', _.bind(btn.click, that));
            });
        },
        _createOverlay: function() {
            var that = this,
                events;

            this.overlay = $('.' + this.options.overlayClass);
            if ( !this.overlay.length ) {
                document.body.style.overflow = 'hidden';
                this.overlay = $('<div></div>')
                    .addClass(this.options.overlayClass)
                    .appendTo( this.options.appendTo );
            } else {
                var zIndex =this.overlay.zIndex();
                this.overlay.zIndex(zIndex + 1);
            }
            events = this.overlay.data('events');
            if ( events ) {
                this.prevOverlayHandler = events.click[0].handler;
            }
            this.overlay.unbind().on('click', function() {
                that.closeDialog();
            });
        },

        _destroyOverlay: function() {
            var dialogCount = this.dialogWrapper.find(this.options.dialogBlock).filter(':visible').length;

            if ( !dialogCount ) {
                document.body.style.overflow = 'auto';
                this.overlay.remove();
                this.overlay = null;
            } else {
                var zIndex =this.overlay.zIndex();
                this.overlay.zIndex(zIndex - 1);
                this.overlay.unbind().on('click', this.prevOverlayHandler);
            }
        },
        _style: function() {
            this.dialog.css({
                padding: '30px',
                backgroundColor: '#fff',
                zIndex: 1000
            });
        },
        _position: function() {
            var type = this.options.type;

            this.dialog.css(this.options.position[type]);
        },
        whichTransitionEvent: function() {
            var transition,
                el = document.createElement('fakeelement'),
                transitions = {
                    'transition': 'transitionend',
                    'OTransition': 'oTransitionEnd',
                    'MozTransition': 'transitionend',
                    'WebkitTransition': 'webkitTransitionEnd'
                };

            for (transition in transitions){
                if ( el.style[transition] !== undefined && transitions.hasOwnProperty(transition) ) {
                    return transitions[transition];
                }
            }
        }
    });

    return $.mage.dialog;
});