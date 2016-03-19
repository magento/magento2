/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mage/template',
    'text!ui/template/modal/modal-popup.html',
    'text!ui/template/modal/modal-slide.html',
    'text!ui/template/modal/modal-custom.html',
    'Magento_Ui/js/lib/key-codes',
    'jquery/ui',
    'mage/translate'
], function ($, _, template, popupTpl, slideTpl, customTpl, keyCodes) {
    'use strict';

    /**
     * Detect browser transition end event.
     * @return {String|undefined} - transition event.
     */
    var transitionEvent =  (function () {
        var transition,
            elementStyle = document.createElement('div').style,
            transitions = {
                'transition': 'transitionend',
                'OTransition': 'oTransitionEnd',
                'MozTransition': 'transitionend',
                'WebkitTransition': 'webkitTransitionEnd'
            };

        for (transition in transitions) {
            if (elementStyle[transition] !== undefined && transitions.hasOwnProperty(transition)) {
                return transitions[transition];
            }
        }
    })();

    /**
     * Modal Window Widget
     */
    $.widget('mage.modal', {
        options: {
            type: 'popup',
            title: '',
            modalClass: '',
            focus: '[data-role="closeBtn"]',
            autoOpen: false,
            clickableOverlay: true,
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
            focusableScope: '[data-role="focusable-scope"]',
            focusableStart: '[data-role="focusable-start"]',
            focusableEnd: '[data-role="focusable-end"]',
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
                attr: {},

                /**
                 * Default action on button click
                 */
                click: function (event) {
                    this.closeModal(event);
                }
            }]
        },
        keyEventHandlers: {

            /**
             * Tab key press handler,
             * set focus to elements
             */
            tabKey: function () {
                if (document.activeElement === this.modal[0]) {
                    this._setFocus('start');
                }
            },

            /**
             * Escape key press handler,
             * close modal window
             */
            escapeKey: function () {
                if (this.options.isOpen && this.modal.find(document.activeElement).length ||
                    this.options.isOpen && this.modal[0] === document.activeElement) {
                    this.closeModal();
                }
            }
        },

        /**
         * Creates modal widget.
         */
        _create: function () {
            _.bindAll(
                this,
                'keyEventSwitcher',
                '_tabSwitcher',
                'closeModal'
            );

            this.options.transitionEvent = transitionEvent;
            this._createWrapper();
            this._renderModal();
            this._createButtons();
            $(this.options.trigger).on('click', _.bind(this.toggleModal, this));
            this._on(this.modal.find(this.options.modalCloseBtn), {
                'click': this.closeModal
            });
            this._on(this.element, {
                'openModal': this.openModal,
                'closeModal': this.closeModal
            });
            this.options.autoOpen ? this.openModal() : false;
        },

        /**
         * Returns element from modal node.
         * @return {Object} - element.
         */
        _getElem: function (elem) {
            return this.modal.find(elem);
        },

        /**
         * Gets visible modal count.
         * * @return {Number} - visible modal count.
         */
        _getVisibleCount: function () {
            var modals = this.modalWrapper.find(this.options.modalBlock);

            return modals.filter('.' + this.options.modalVisibleClass).length;
        },

        /**
         * Gets count of visible modal by slide type.
         * * @return {Number} - visible modal count.
         */
        _getVisibleSlideCount: function () {
            var elems = this.modalWrapper.find('[data-type="slide"]');

            return elems.filter('.' + this.options.modalVisibleClass).length;
        },

        /**
         * Listener key events.
         * Call handler function if it exists
         */
        keyEventSwitcher: function (event) {
            var key = keyCodes[event.keyCode];

            if (this.keyEventHandlers.hasOwnProperty(key)) {
                this.keyEventHandlers[key].apply(this, arguments);
            }
        },

        /**
         * Toggle modal.
         * * @return {Element} - current element.
         */
        toggleModal: function () {
            if (this.options.isOpen === true) {
                this.closeModal();
            } else {
                this.openModal();
            }
        },

        /**
         * Open modal.
         * * @return {Element} - current element.
         */
        openModal: function () {
            this.options.isOpen = true;
            this.focussedElement = document.activeElement;
            this._createOverlay();
            this._setActive();
            this._setKeyListener();
            this.modal.one(this.options.transitionEvent, _.bind(this._trigger, this, 'opened'));
            this.modal.one(this.options.transitionEvent, _.bind(this._setFocus, this, 'end', 'opened'));
            this.modal.addClass(this.options.modalVisibleClass);

            if (!this.options.transitionEvent) {
                this._trigger('opened');
            }

            return this.element;
        },

        /**
         * Set focus to element.
         * @param {String} position - can be "start" and "end"
         *      positions.
         *      If position is "end" - sets focus to first
         *      focusable element in modal window scope.
         *      If position is "start" - sets focus to last
         *      focusable element in modal window scope
         *
         *  @param {String} type - can be "opened" or false
         *      If type is "opened" - looks to "this.options.focus"
         *      property and sets focus
         */
        _setFocus: function (position, type) {
            var focusableElements,
                infelicity;

            if (type === 'opened' && this.options.focus) {
                this.modal.find($(this.options.focus)).focus();
            } else if (type === 'opened' && !this.options.focus) {
                this.modal.find(this.options.focusableScope).focus();
            } else if (position === 'end') {
                this.modal.find(this.options.modalCloseBtn).focus();
            } else if (position === 'start') {
                infelicity = 2; //Constant for find last focusable element
                focusableElements = this.modal.find(':focusable');
                focusableElements.eq(focusableElements.length - infelicity).focus();
            }
        },

        /**
         * Set events listener when modal is opened.
         */
        _setKeyListener: function () {
            this.modal.find(this.options.focusableStart).bind('focusin', this._tabSwitcher);
            this.modal.find(this.options.focusableEnd).bind('focusin', this._tabSwitcher);
            this.modal.bind('keydown', this.keyEventSwitcher);
        },

        /**
         * Remove events listener when modal is closed.
         */
        _removeKeyListener: function () {
            this.modal.find(this.options.focusableStart).unbind('focusin', this._tabSwitcher);
            this.modal.find(this.options.focusableEnd).unbind('focusin', this._tabSwitcher);
            this.modal.unbind('keydown', this.keyEventSwitcher);
        },

        /**
         * Switcher for focus event.
         * @param {Object} e - event
         */
        _tabSwitcher: function (e) {
            var target = $(e.target);

            if (target.is(this.options.focusableStart)) {
                this._setFocus('start');
            } else if (target.is(this.options.focusableEnd)) {
                this._setFocus('end');
            }
        },

        /**
         * Close modal.
         * * @return {Element} - current element.
         */
        closeModal: function () {
            var that = this;

            this._removeKeyListener();
            this.options.isOpen = false;
            this.modal.one(this.options.transitionEvent, function () {
                that._close();
            });
            this.modal.removeClass(this.options.modalVisibleClass);

            if (!this.options.transitionEvent) {
                that._close();
            }

            return this.element;
        },

        /**
         * Helper for closeModal function.
         */
        _close: function () {
            var trigger = _.bind(this._trigger, this, 'closed', this.modal);

            $(this.focussedElement).focus();
            this._destroyOverlay();
            this._unsetActive();
            _.defer(trigger, this);
        },

        /**
         * Set z-index and margin for modal and overlay.
         */
        _setActive: function () {
            var zIndex = this.modal.zIndex();

            this.prevOverlayIndex = this.overlay.zIndex();
            this.modal.zIndex(zIndex + this._getVisibleCount());
            this.overlay.zIndex(zIndex + (this._getVisibleCount() - 1));

            if (this._getVisibleSlideCount()) {
                this.modal.css('marginLeft', this.options.modalLeftMargin * this._getVisibleSlideCount());
            }
        },

        /**
         * Unset styles for modal and set z-index for previous modal.
         */
        _unsetActive: function () {
            this.modal.removeAttr('style');

            if (this.overlay) {
                this.overlay.zIndex(this.prevOverlayIndex);
            }
        },

        /**
         * Creates wrapper to hold all modals.
         */
        _createWrapper: function () {
            this.modalWrapper = $('.' + this.options.wrapperClass);

            if (!this.modalWrapper.length) {
                this.modalWrapper = $('<div></div>')
                    .addClass(this.options.wrapperClass)
                    .appendTo(this.options.appendTo);
            }
        },

        /**
         * Compile template and append to wrapper.
         */
        _renderModal: function () {
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
        _createButtons: function () {
            var that = this;

            this.buttons = this._getElem(this.options.modalAction);
            _.each(this.options.buttons, function (btn, key) {
                var button = that.buttons[key];

                if (btn.attr) {
                    $(button).attr(btn.attr);
                }
                $(button).on('click', _.bind(btn.click, that));
            });
        },

        /**
         * Creates overlay, append it to wrapper, set previous click event on overlay.
         */
        _createOverlay: function () {
            var events;

            this.overlay = $('.' + this.options.overlayClass);

            if (!this.overlay.length) {
                $(this.options.appendTo).addClass(this.options.parentModalClass);
                this.overlay = $('<div></div>')
                    .addClass(this.options.overlayClass)
                    .appendTo(this.modalWrapper);
            }
            events = $._data(this.overlay.get(0), 'events');
            events ? this.prevOverlayHandler = events.click[0].handler : false;
            this.options.clickableOverlay ? this.overlay.unbind().on('click', this.closeModal) : false;
        },

        /**
         * Destroy overlay.
         */
        _destroyOverlay: function () {
            if (this._getVisibleCount()) {
                this.overlay.unbind().on('click', this.prevOverlayHandler);
            } else {
                $(this.options.appendTo).removeClass(this.options.parentModalClass);
                this.overlay.remove();
                this.overlay = null;
            }
        }
    });

    return $.mage.modal;
});
