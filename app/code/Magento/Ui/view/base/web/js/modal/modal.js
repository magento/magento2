/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "underscore",
    "mage/template",
    "text!ui/template/modal/modal-popup.html",
    "text!ui/template/modal/modal-slide.html",
    "text!ui/template/modal/modal-custom.html",
    "jquery/ui",
    "mage/translate"
], function($, _, template, popupTpl, slideTpl, customTpl){
    "use strict";

    /**
     * Modal Window Widget
     */
    $.widget('mage.modal', {
        options: {
            type: 'popup',
            title: '',
            modalClass: '',
            popupTpl: popupTpl,
            slideTpl: slideTpl,
            customTpl: customTpl,
            modalVisibleClass: '_show',
            parentModalClass: '_has-modal',
            innerScrollClass: '_inner-scroll',
            responsive: false,
            innerScroll: false,
            modalBlock: '[data-role="modal"]',
            modalCloseBtn: '[data-role="closeBtn"]',
            modalContent: '[data-role="content"]',
            modalAction: '[data-role="action"]',
            appendTo: 'body',
            wrapperClass: 'modals-wrapper',
            overlayClass: 'modals-overlay',
            responsiveClass: 'modal-slide',
            trigger: '',
            modalLeftMargin: 45,
            closeText: $.mage.__('Close'),
            buttons: [{
                text: $.mage.__('Ok'),
                class: '',
                click: function(){
                    this.closeModal();
                }
            }]
        },
        /**
         * Creates modal widget.
         */
        _create: function() {
            this.options.transitionEvent = this.whichTransitionEvent();
            this._createWrapper();
            this._renderModal();
            this._createButtons();

            this.modal.find(this.options.modalCloseBtn).on('click',  _.bind(this.closeModal, this));
            $(this.options.trigger).on('click', _.bind(this.toggleModal, this));
            this.element.on('openModal', _.bind(this.openModal, this));
            this.element.on('closeModal', _.bind(this.closeModal, this));
        },
        /**
         * Returns element from modal node.
         * @return {Object} - element.
         */
        _getElem: function(elem) {
            return this.modal.find(elem);
        },
        /**
         * Gets visible modal count.
         * * @return {Number} - visible modal count.
         */
        _getVisibleCount: function() {
            return this.modalWrapper.find('.'+this.options.modalVisibleClass).length;
        },
        /**
         * Gets count of visible modal by slide type.
         * * @return {Number} - visible modal count.
         */
        _getVisibleSlideCount: function() {
            var elems = this.modalWrapper.find('[data-type="slide"]');

            return elems.filter('.'+this.options.modalVisibleClass).length;
        },
        toggleModal: function() {
            if (this.options.isOpen == true) {
                this.closeModal();
            } else {
                this.openModal();
            }
        },
        openModal: function() {
            var that = this;

            this.options.isOpen = true;
            this._createOverlay();
            this._setActive();
            this.modal.one(this.options.transitionEvent, function() {
                that._trigger('opened');
            });
            this.modal.addClass(this.options.modalVisibleClass);
            if ( !this.options.transitionEvent ) {
                that._trigger('opened');
            }

            return this.element;
        },
        closeModal: function() {
            var that = this;

            this.options.isOpen = false;
            this.modal.one(this.options.transitionEvent, function() {
                that._close();
            });
            this.modal.removeClass(this.options.modalVisibleClass);
            if ( !this.options.transitionEvent ) {
                that._close();
            }

            return this.element;
        },
        /**
         * Helper for closeModal function.
         */
        _close: function() {
            var trigger = _.bind(this._trigger, this, 'closed', this.modal);

            this._destroyOverlay();
            this._unsetActive();
            _.defer(trigger, this);
        },
        /**
         * Set z-index and margin for modal and overlay.
         */
        _setActive: function() {
            var zIndex = this.modal.zIndex();

            this.prevOverlayIndex = this.overlay.zIndex();
            this.modal.zIndex(zIndex + this._getVisibleCount());
            this.overlay.zIndex(zIndex + (this._getVisibleCount() - 1));
            if ( this._getVisibleSlideCount() ) {
                this.modal.css('marginLeft', this.options.modalLeftMargin * this._getVisibleSlideCount());
            }
        },
        /**
         * Unset styles for modal and set z-index for previous modal.
         */
        _unsetActive: function() {
            this.modal.removeAttr('style');
            if ( this.overlay ) {
                this.overlay.zIndex(this.prevOverlayIndex);
            }
        },
        /**
         * Creates wrapper to hold all modals.
         */
        _createWrapper: function() {
            this.modalWrapper = $('.'+this.options.wrapperClass);
            if ( !this.modalWrapper.length ) {
                this.modalWrapper = $('<div></div>')
                     .addClass(this.options.wrapperClass)
                     .appendTo(this.options.appendTo);
            }
        },
        /**
         * Compile template and append to wrapper.
         */
        _renderModal: function() {
            $(template(
                this.options[this.options.type + 'Tpl'],
                {
                    data: this.options
                })).appendTo(this.modalWrapper);
            this.modal = this.modalWrapper.find(this.options.modalBlock).last();
            this.element.show().appendTo(this._getElem(this.options.modalContent));
        },
        /**
         * Creates buttons pane.
         */
        _createButtons: function() {
            var that = this;

            this.buttons = this._getElem(this.options.modalAction);
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
                $(this.options.appendTo).addClass(this.options.parentModalClass);
                this.overlay = $('<div></div>')
                    .addClass(this.options.overlayClass)
                    .appendTo(this.modalWrapper);
            }

            events = $._data(this.overlay.get(0), 'events');
            if ( events ) {
                this.prevOverlayHandler = events.click[0].handler;
            }
            this.overlay.unbind().on('click', function() {
                that.closeModal();
            });
        },
        /**
         * Destroy overlay.
         */
        _destroyOverlay: function() {
            var modalCount = this.modalWrapper.find('.'+this.options.modalVisibleClass).length;

            if ( !modalCount ) {
                $(this.options.appendTo).removeClass(this.options.parentModalClass);
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

    return $.mage.modal;
});
