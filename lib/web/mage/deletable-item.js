/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function($){

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
});