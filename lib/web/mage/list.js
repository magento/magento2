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
 /*global Handlebars*/
define([
    "jquery",
    "jquery/ui"
], function($){
    "use strict";

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

        _create: function() {

            this.options.itemCount = this.options.itemIndex = 0;

            var that = this,
                options = this.options,
                destination = $(options.destinationSelector),
                addButton = this.element.find($(options.addButton));

            this.element
                .addClass('list-widget');

            addButton.bind('click', $.proxy(this.handleAdd, this));

            //handle remove
            destination.on('click', this.options.removeButton, $.proxy(this.removeItem, this));
        },

        handleAdd: function() {
            this.addItem(++this.options.itemIndex);
            return false;
        },

        addItem: function(index, parent) {
            var options = this.options,
                template = $(options.template),
                destination = $(options.destinationSelector),
                item = $(options.templateWrapper);

            item.addClass(this.options.templateClass)
                .attr('id', 'list-item-'+ index)
                .attr('data-role', 'addedItem')
                .attr('data-parent', parent);


            var source = template.html(),
                preTemplate = Handlebars.compile(source),
                context = this.handleContext(index),
                compiledTemplate = preTemplate(context);

            item.append(compiledTemplate);
            destination.append(item);

            this.checkLimit(++this.options.itemCount);
            return item;
        },

        handleContext: function(index) {
            var context = {_index_: index};
            return context;
        },

        removeItem: function(e) {
            $(e.currentTarget).closest('[data-role="addedItem"]').remove();

            this.checkLimit(--this.options.itemCount);
            return false;
        },

        checkLimit: function(index) {
            var addButton = $(this.options.addButton),
                maxItems = this.options.maxItems,
                maxItemsAlert = $(this.options.maxItemsAlert);

            if (maxItems !== null && index >= maxItems) {
                addButton.hide();
                maxItemsAlert.show();
            } else if (addButton.is(":hidden")) {
                addButton.show();
                maxItemsAlert.hide();
            }
        },

        _destroy: function() {

            var destination = $(this.options.destinationSelector);

            this.element
                .removeClass('list-widget');

            destination
                .find('[data-role="addedItem"]')
                .remove();
        }

    });

});