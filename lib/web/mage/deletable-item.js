/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";
    
    /**
     * This widget is used to tag a DOM element as deletable. By default, it will use the click event on the item with a
     * data role of delete to trigger the deletion.
     */
    $.widget('mage.deletableItem', {
        options: {
            deleteEvent: 'click',
            deleteSelector: '[data-role="delete"]',
            hiddenClass: 'no-display'
        },

        /**
         * This method binds elements found in this widget.
         */
        _bind: function () {
            var handlers = {};

            // since the first handler is dynamic, generate the object using array notation
            handlers[this.options.deleteEvent + ' ' + this.options.deleteSelector] = '_onDeleteClicked';
            handlers.hideDelete = '_onHideDelete';
            handlers.showDelete = '_onShowDelete';

            this._on(handlers);
        },

        /**
         * This method constructs a new widget.
         */
        _create: function () {
            this._bind();
        },

        /**
         * This method is to initialize the control
         * @private
         */
        _init: function() {
            this._onHideDelete(); // by default, hide the control
        },

        /**
         * This method removes the entity from the DOM.
         * @private
         */
        _onDeleteClicked: function (e) {
            e.stopPropagation();
            this.element.trigger('deleteItem');
        },

        /**
         * This method hides the delete capability of this item (i.e. making it not deletable)
         * @private
         */
        _onHideDelete: function () {
            this.element.find(this.options.deleteSelector).addClass(this.options.hiddenClass);
        },

        /**
         * This method shows the delete capability of this item (i.e. making it deletable)
         * @private
         */
        _onShowDelete: function () {
            this.element.find(this.options.deleteSelector).removeClass(this.options.hiddenClass);
        }
    });
    
    return $.mage.deletableItem;
});
