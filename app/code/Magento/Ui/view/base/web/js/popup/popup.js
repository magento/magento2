/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "underscore",
    "mage/template",
    "text!ui/template/popup/popup-modal.html",
    "text!ui/template/popup/popup-slide.html",
    "jquery/ui",
    "mage/translate"
], function($, _, template, modalTpl, slideTpl){
    "use strict";

    /**
     * Dialog Widget
     */
    $.widget('mage.popup', {
        options: {
            type: 'modal',
            title: '',
            dialogClass: '',
            modalTpl: modalTpl,
            slideTpl: slideTpl,
            dialogVisibleClass: '_show',
            parentDialogClass: '_has-dialog',
            innerScrollClass: '_inner-scroll',
            responsive: false,
            innerScroll: false,
            dialogBlock: '[data-role="dialog"]',
            dialogCloseBtn: '[data-role="closeBtn"]',
            dialogContent: '[data-role="content"]',
            dialogAction: '[data-role="action"]',
            appendTo: 'body',
            wrapperClass: 'dialogs-wrapper',
            overlayClass: 'overlay_magento',
            responsiveClass: 'dialog-slide',
            dialogLeftMargin: 45,
            closeText: $.mage.__('Close'),
            buttons: [{
                text: $.mage.__('Ok'),
                class: '',
                click: function(){
                    this.closeDialog();
                }
            }]
        },
        /**
         * Creates dialog widget.
         */
        _create: function() {
            this.options.transitionEvent = this.whichTransitionEvent();
            this._createWrapper();
            this._renderDialog();
            this._createButtons();

            this.dialog.find(this.options.dialogCloseBtn).on('click',  _.bind(this.closeDialog, this));
            this.element.on('openDialog', _.bind(this.openDialog, this));
            this.element.on('closeDialog', _.bind(this.closeDialog, this));
        },
        /**
         * Returns element from dialog node.
         * @return {Object} - element.
         */
        _getElem: function(elem) {
            return this.dialog.find(elem);
        },
        /**
         * Gets visible dialog count.
         * * @return {Number} - visible dialog count.
         */
        _getVisibleCount: function() {
            return this.dialogWrapper.find('.'+this.options.dialogVisibleClass).length;
        },
        /**
         * Gets visible slide type dialog count.
         * * @return {Number} - visible dialog count.
         */
        _getVisibleSlideCount: function() {
            var elems = this.dialogWrapper.find('[data-type="slide"]');

            return elems.filter('.'+this.options.dialogVisibleClass).length;
        },
        openDialog: function() {
            var that = this;

            this.options.isOpen = true;
            this._createOverlay();
            this._setActive();
            this.dialog.one(this.options.transitionEvent, function() {
                that._trigger('opened');
            });
            this.dialog.addClass(this.options.dialogVisibleClass);
            if ( !this.options.transitionEvent ) {
                that._trigger('opened');
            }

            return this.element;
        },
        closeDialog: function() {
            var that = this;

            this.options.isOpen = false;
            this.dialog.one(this.options.transitionEvent, function() {
                that._close();
            });
            this.dialog.removeClass(this.options.dialogVisibleClass);
            if ( !this.options.transitionEvent ) {
                that._close();
            }

            return this.element;
        },
        /**
         * Helper for closeDialog function.
         */
        _close: function() {
            var trigger = _.bind(this._trigger, this, 'closed', this.dialog);

            this._destroyOverlay();
            this._unsetActive();
            _.defer(trigger, this);
        },
        /**
         * Set z-index and margin for dialog and overlay.
         */
        _setActive: function() {
            var zIndex = this.dialog.zIndex();

            this.prevOverlayIndex = this.overlay.zIndex();
            this.dialog.zIndex(zIndex + this._getVisibleCount());
            this.overlay.zIndex(zIndex + (this._getVisibleCount() - 1));
            if ( this._getVisibleSlideCount() ) {
                this.dialog.css('marginLeft', this.options.dialogLeftMargin * this._getVisibleSlideCount());
            }
        },
        /**
         * Unset styles for dialog and set z-index for previous dialog.
         */
        _unsetActive: function() {
            this.dialog.removeAttr('style');
            if ( this.overlay ) {
                this.overlay.zIndex(this.prevOverlayIndex);
            }
        },
        /**
         * Creates wrapper to hold all dialogs.
         */
        _createWrapper: function() {
            this.dialogWrapper = $('.'+this.options.wrapperClass);
            if ( !this.dialogWrapper.length ) {
                this.dialogWrapper = $('<div></div>')
                     .addClass(this.options.wrapperClass)
                     .appendTo(this.options.appendTo);
            }
        },
        /**
         * Compile template and append to wrapper.
         */
        _renderDialog: function() {
            $(template(
                this.options[this.options.type + 'Tpl'],
                {
                    data: this.options
                })).appendTo(this.dialogWrapper);
            this.dialog = this.dialogWrapper.find(this.options.dialogBlock).last();
            this.element.show().appendTo(this._getElem(this.options.dialogContent));
        },
        /**
         * Creates buttons pane.
         */
        _createButtons: function() {
            var that = this;

            this.buttons = this._getElem(this.options.dialogAction);
            _.each(this.options.buttons, function(btn, key) {
                var button = that.buttons[key];

                $(button).on('click', _.bind(btn.click, that));
            });
        },
        /**
         * Creates overlay, append it to wrapper, set previous click event on overlay.
         */
        _createOverlay: function() {
            var that = this,
                events;

            this.overlay = $('.' + this.options.overlayClass);
            if ( !this.overlay.length ) {
                $(this.options.appendTo).addClass(this.options.parentDialogClass);
                this.overlay = $('<div></div>')
                    .addClass(this.options.overlayClass)
                    .appendTo(this.dialogWrapper);
            }

            events = $._data(this.overlay.get(0), 'events');
            if ( events ) {
                this.prevOverlayHandler = events.click[0].handler;
            }
            this.overlay.unbind().on('click', function() {
                that.closeDialog();
            });
        },
        /**
         * Destroy overlay.
         */
        _destroyOverlay: function() {
            var dialogCount = this.dialogWrapper.find('.'+this.options.dialogVisibleClass).length;

            if ( !dialogCount ) {
                $(this.options.appendTo).removeClass(this.options.parentDialogClass);
                this.overlay.remove();
                this.overlay = null;

            } else {
                this.overlay.unbind().on('click', this.prevOverlayHandler);
            }
        },
        /**
         * Detects browser transition event.
         */
        whichTransitionEvent: function() {
            var transition,
                el = document.createElement('element'),
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

    return $.mage.popup;
});
