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
            dialogActiveClass: '_show',
            parentDialogClass: '_has-dialog',
            overlayClass: 'overlay_magento',
            responsiveClass: 'dialog-slide',
            responsive: false,
            dialogBlock: '[data-role="dialog"]',
            dialogCloseBtn: '[data-role="closeBtn"]',
            dialogContent: '[data-role="content"]',
            dialogAction: '[data-role="action"]',
            appendTo: 'body',
            wrapperId: 'dialogs-wrapper'
        },

        _create: function() {
            this.options.transitionEvent = this.whichTransitionEvent();
            this._createWrapper();
            this._renderDialog();
            this._createButtons();

            this.dialog.find(this.options.dialogCloseBtn).on('click',  _.bind(this.closeDialog, this));
            this.element.on('openDialog', _.bind(this.openDialog, this));
            this.element.on('closeDialog', _.bind(this.closeDialog, this));
        },
        _getElem: function(elem) {
            return this.dialog.find(elem);
        },
        openDialog: function() {
            this.options.isOpen = true;
            this._createOverlay();
            this.dialog.show();
            this.dialog.addClass(this.options.dialogActiveClass);

            return this.element;
        },
        closeDialog: function() {
            var that = this;

            this.options.isOpen = false;
            this.dialog.one(this.options.transitionEvent, function() {
                that.dialog.removeClass(that.options.dialogActiveClass);
                that._close();
            });
            this.dialog.removeClass(this.options.dialogActiveClass);
            if ( !this.options.transitionEvent ) {
                that.dialog.removeClass(this.options.dialogActiveClass);
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

                $(this.options.appendTo).addClass(this.options.parentDialogClass);
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
            var dialogCount = this.dialogWrapper.find(this.options.dialogBlock).filter(this.option.dialogClass).length;

            if ( !dialogCount ) {

                $(this.options.appendTo).removeClass(this.options.parentDialogClass);

                this.overlay.remove();
                this.overlay = null;
            } else {
                var zIndex =this.overlay.zIndex();
                this.overlay.zIndex(zIndex - 1);
                this.overlay.unbind().on('click', this.prevOverlayHandler);
            }
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