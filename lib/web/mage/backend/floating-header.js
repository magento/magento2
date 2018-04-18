/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
        
    $.widget('mage.floatingHeader', {
        options: {
            placeholderAttrs: {
                'class': 'page-actions-placeholder'
            },
            fixedClass: '_fixed',
            hiddenClass: '_hidden',
            title: '.page-title-wrapper .page-title',
            pageMainActions: '.page-main-actions',
            contains: '[data-role=modal]'
        },

        /**
         * Widget initialization
         * @private
         */
        _create: function() {
            var title = $(this.options.title).text(),
                wrapped = this.element.find('.page-actions-buttons').children();

            if (this.element.parents(this.options.contains).length) {
                return this;
            }

            this._setVars();
            this._bind();
            this.element.find('script').remove();
            if (wrapped.length) {
                wrapped
                    .unwrap()   // .page-actions-buttons
                    .unwrap();  // .page-actions-inner
            }
            this.element.wrapInner($('<div/>', {'class': 'page-actions-buttons'}));
            this.element.wrapInner($('<div/>', {'class': 'page-actions-inner', 'data-title': title}));
        },

        /**
         * Set privat variables on load, for performance purposes
         * @private
         */
        _setVars: function() {
            this._placeholder = this.element.before($('<div/>', this.options.placeholderAttrs)).prev();
            this._offsetTop = this._placeholder.offset().top;
            this._height = this.element
                .parents(this.options.pageMainActions)
                .outerHeight();
        },

        /**
         * Event binding, will monitor scroll and resize events (resize events left for backward compat)
         * @private
         */
        _bind: function() {
            this._on(window, {
                scroll: this._handlePageScroll,
                resize: this._handlePageScroll
            });
        },

        /**
         * Event handler for setting fixed positioning
         * @event
         * @private
         */
        _handlePageScroll: function() {
            var isActive = ($(window).scrollTop() > this._offsetTop);

            if (isActive) {
                this.element
                    .addClass(this.options.fixedClass)
                    .parents(this.options.pageMainActions)
                    .addClass(this.options.hiddenClass);
            } else {
                this.element
                    .removeClass(this.options.fixedClass)
                    .parents(this.options.pageMainActions)
                    .removeClass(this.options.hiddenClass);
            }

            this._placeholder.height(isActive ? this._height: '');
        },

        /**
         * Widget destroy functionality
         * @private
         */
        _destroy: function() {
            this._placeholder.remove();
            this._off($(window));
        }
    });
    
    return $.mage.floatingHeader;
});
