/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/template',
    'jquery/ui'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.list', {
        options: {
            template: '[data-role=item]', //template for item,
            templateWrapper: null, //template wrapper
            templateClass: null, //template wrapper class
            destinationSelector: '[data-role=container]', //destination selector of list
            itemIndex: 0, //setting an item index
            itemCount: 0, //get count of items
            addButton: '[data-button=add]', //button for adding list item
            removeButton: '[data-button=remove]', //button for removing list item
            maxItems: null,
            maxItemsAlert: null
        },

        /**
         * @private
         */
        _create: function () {
            var options, destination, addButton;

            this.options.itemCount = this.options.itemIndex = 0;

            options = this.options;
            destination = $(options.destinationSelector);
            addButton = this.element.find($(options.addButton));

            this.element
                .addClass('list-widget');

            addButton.bind('click', $.proxy(this.handleAdd, this));

            //handle remove
            destination.on('click', this.options.removeButton, $.proxy(this.removeItem, this));
        },

        /**
         * @return {Boolean}
         */
        handleAdd: function () {
            this.addItem(++this.options.itemIndex);

            return false;
        },

        /**
         * @param {*} index
         * @param {*} parent
         * @return {*|jQuery|HTMLElement}
         */
        addItem: function (index, parent) {
            var options = this.options,
                template = $(options.template),
                destination = $(options.destinationSelector),
                item = $(options.templateWrapper),
                source, preTemplate, context, compiledTemplate;

            item.addClass(this.options.templateClass)
                .attr('id', 'list-item-' + index)
                .attr('data-role', 'addedItem')
                .attr('data-parent', parent);

            source = template.html();
            preTemplate = mageTemplate(source);
            context = this.handleContext(index);
            compiledTemplate = preTemplate({
                data: context
            });

            item.append(compiledTemplate);
            destination.append(item);

            this.checkLimit(++this.options.itemCount);

            return item;
        },

        /**
         * @param {*} index
         * @return {Object}
         */
        handleContext: function (index) {
            var context = {
                _index_: index
            };

            return context;
        },

        /**
         * @param {jQuery.Event} e
         * @return {Boolean}
         */
        removeItem: function (e) {
            $(e.currentTarget).closest('[data-role="addedItem"]').remove();

            this.checkLimit(--this.options.itemCount);

            return false;
        },

        /**
         * @param {*} index
         */
        checkLimit: function (index) {
            var addButton = $(this.options.addButton),
                maxItems = this.options.maxItems,
                maxItemsAlert = $(this.options.maxItemsAlert);

            if (maxItems !== null && index >= maxItems) {
                addButton.hide();
                maxItemsAlert.show();
            } else if (addButton.is(':hidden')) {
                addButton.show();
                maxItemsAlert.hide();
            }
        },

        /**
         * @private
         */
        _destroy: function () {
            var destination = $(this.options.destinationSelector);

            this.element
                .removeClass('list-widget');
            destination
                .find('[data-role="addedItem"]')
                .remove();
        }
    });

    return $.mage.list;
});
