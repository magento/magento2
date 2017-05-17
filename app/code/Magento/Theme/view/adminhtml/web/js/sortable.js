/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * JQuery UI Widget declaration: 'mage.sortable'
 *
 * @api
 */
define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    /**
     * Widget panel
     */
    $.widget('mage.sortable', $.ui.sortable, {
        options: {
            moveUpEvent:   'moveUp',
            moveDownEvent: 'moveDown'
        },

        /** @inheritdoc */
        _create: function () {
            this._super();
            this.initButtons();
            this.bind();
        },

        /**
         * Init buttons.
         */
        initButtons: function () {
            this.element.find('input.up').on('click', $.proxy(function (event) {
                $('body').trigger(this.options.moveUpEvent, {
                    item: $(event.target).parent('li')
                });
            }, this));
            this.element.find('input.down').on('click', $.proxy(function (event) {
                $('body').trigger(this.options.moveDownEvent, {
                    item: $(event.target).parent('li')
                });
            }, this));
        },

        /**
         * Bind.
         */
        bind: function () {
            var $body = $('body');

            $body.on(this.options.moveUpEvent, $.proxy(this._onMoveUp, this));
            $body.on(this.options.moveDownEvent, $.proxy(this._onMoveDown, this));
        },

        /**
         * @param {jQuery.Event} event
         * @param {Object} data
         * @private
         */
        _onMoveUp: function (event, data) {
            data.item.insertBefore(data.item.prev());
        },

        /**
         * @param {jQuery.Event} event
         * @param {Object} data
         * @private
         */
        _onMoveDown: function (event, data) {
            data.item.insertAfter(data.item.next());
        }
    });

    return $.mage.sortable;
});
