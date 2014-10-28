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
define(["jquery","jquery/ui"], function($){
    /**
     * Widget panel
     */
    $.widget('mage.sortable', $.ui.sortable, {
        options: {
            moveUpEvent:   'moveUp',
            moveDownEvent: 'moveDown'
        },

        _create: function() {
            this._super();
            this.initButtons();
            this.bind();
        },

        initButtons: function() {
            this.element.find('input.up').on('click', $.proxy(function(event){
                $('body').trigger(this.options.moveUpEvent, {item:$(event.target).parent('li')});
            }, this));
            this.element.find('input.down').on('click', $.proxy(function(event){
                $('body').trigger(this.options.moveDownEvent, {item:$(event.target).parent('li')});
            }, this));
        },

        bind: function() {
            var $body = $('body');
            $body.on(this.options.moveUpEvent, $.proxy(this._onMoveUp, this));
            $body.on(this.options.moveDownEvent, $.proxy(this._onMoveDown, this));
        },

        _onMoveUp: function(event, data) {
            data.item.insertBefore(data.item.prev());
        },

        _onMoveDown: function(event, data) {
            data.item.insertAfter(data.item.next());
        }
    });

});