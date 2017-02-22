/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    'jquery',
    'mage/template',
    'jquery/ui'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.itemTable', {
        options: {
            addBlock: '[data-template="add-block"]',
            addBlockData: {},
            addEvent: 'click',
            addSelector: '[data-role="add"]',
            itemsSelector: '[data-container="items"]',
            keepLastRow: true
        },

        /**
         * This method adds a new instance of the block to the items.
         * @private
         */
        _add: function () {
            var hideShowDelete,
                deletableItems,
                addedBlock;

            // adding a new row, so increment the count to give each row a unique index
            this.rowIndex++;

            // make sure the block data has the rowIndex
            this.options.addBlockData.rowIndex = this.rowIndex;

            // render the form
            addedBlock = $(this.addBlockTmpl({
                data: this.options.addBlockData
            }));

            // add the row to the item block
            this.element.find(this.options.itemsSelector).append(addedBlock);

            // initialize all mage content
            addedBlock.trigger('contentUpdated');

            // determine all existing items in the collection
            deletableItems = this._getDeletableItems();

            // for the most part, show the delete mechanism, except in the case where there is only one it should not
            // be deleted
            hideShowDelete = 'showDelete';

            if (this.options.keepLastRow && deletableItems.length === 1) {
                hideShowDelete = 'hideDelete';
            }

            // loop through each control and perform that action on the deletable item
            $.each(deletableItems, function (index) {
                $(deletableItems[index]).trigger(hideShowDelete);
            });
        },

        /**
         * This method binds elements found in this widget.
         * @private
         */
        _bind: function () {
            var handlers = {};

            // since the first handler is dynamic, generate the object using array notation
            handlers[this.options.addEvent + ' ' + this.options.addSelector] = '_add';
            handlers.deleteItem = '_onDeleteItem';

            this._on(handlers);
        },

        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this._bind();

            this.addBlockTmpl = mageTemplate(this.options.addBlock);

            // nothing in the table, so indicate that
            this.rowIndex = -1;

            // make sure the block data is an object
            if (this.options.addBlockData == null || typeof this.options.addBlockData !== 'object') {
                // reset the block data to an empty object
                this.options.addBlockData = {};
            }

            // add the first row to the table
            this._add();
        },

        /**
         * This method returns the list of widgets associated with deletable items from the container (direct children
         * only).
         * @private
         */
        _getDeletableItems: function () {
            return this.element.find(this.options.itemsSelector + '> .deletableItem');
        },

        /**
         * This method removes the item associated with the message.
         * @private
         */
        _onDeleteItem: function (e) {
            // parent elements don't need to see this event
            e.stopPropagation();

            // remove the deletable item
            $(e.target).remove();

            if (this.options.keepLastRow) {
                // determine if there is only one element remaining, in which case, disable the delete mechanism on it
                var deletableItems = this._getDeletableItems();

                if (deletableItems.length === 1) {
                    $(deletableItems[0]).trigger('hideDelete');
                }
            }
        }
    });

    return $.mage.itemTable;
});
