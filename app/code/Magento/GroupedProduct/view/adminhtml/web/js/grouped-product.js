/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "jquery/template",
    "mage/adminhtml/grid"
], function($){
    'use strict';

    $.widget('mage.groupedProduct', {
        /**
         * Create widget
         * @private
         */
        _create: function () {
            this.$grid = this.element.find('[data-role=grouped-product-grid]');
            this.$grid.sortable({
                distance: 8,
                items: '[data-role=row]',
                tolerance: "pointer",
                cancel: ':input',
                update: $.proxy(function() {
                    this.element.trigger('resort');
                }, this)
            });

            this.$template = this.element.find('#group-product-template');
            $.each(
                this.$grid.data('products'),
                $.proxy(function(index, product) {
                    this._add(null, product);
                }, this)
            );

            this._on({
                'add': '_add',
                'resort': '_resort',
                'click [data-column=actions] [data-role=delete]': '_remove'
            });

            this._bindDialog();
            this._updateGridVisibility();
        },

        /**
         * Add product to grouped grid
         * @param event
         * @param product
         * @private
         */
        _add: function(event, product) {
            var productExists = this.$grid.find('[data-role=id]')
                .filter(function(index, element) { return $(element).val() == product.id; }).length;
            if (!productExists) {
                this.$template.tmpl(product).appendTo(this.$grid.find('tbody'));
            }
        },

        /**
         * Remove product
         * @param event
         * @private
         */
        _remove: function(event) {
            $(event.target).closest('[data-role=row]').remove();
            this.element.trigger('resort');
            this._updateGridVisibility();
        },

        /**
         * Resort products
         * @private
         */
        _resort: function() {
            this.element.find('[data-role=position]').each($.proxy(function(index, element) {
                $(element).val(index + 1);
            }, this));
        },

        /**
         * Create dialog for show product
         *
         * @private
         */
        _bindDialog: function () {
            var widget = this;
            var selectedProductList = {};
            var popup =  $('[data-role=add-product-dialog]');
            popup.dialog({
                title: $.mage.__('Add Products to Group'),
                autoOpen: false,
                minWidth: 980,
                modal: true,
                resizable: true,
                dialogClass: 'grouped',
                buttons: [{
                    id: 'grouped-product-dialog-cancel-button',
                    text: $.mage.__('Cancel'),
                    click: function () {
                        $(this).dialog('close');
                    }
                }, {
                    id: 'grouped-product-dialog-apply-button',
                    text: $.mage.__('Add Selected Products'),
                    'class': 'add primary',
                    click: function () {
                       $.each(selectedProductList, function(index, product) {
                            widget._add(null, product);
                       });
                       widget._resort();
                       widget._updateGridVisibility();
                       $(this).dialog('close');
                    }
                }]
            });

            popup.on('click', '[data-role=row]', function(event) {
                var target = $(event.target);
                if (!target.is('input')) {
                    target.closest('[data-role=row]')
                        .find('[data-column=entity_id] input')
                        .prop('checked', function(element, value) { return !value; })
                        .trigger('change');
                }
            });

            popup.on(
                'change',
                '[data-role=row] [data-column=entity_id] input',
                $.proxy(function(event) {
                    var element = $(event.target);
                    var product = {};
                    if (element.is(':checked')) {
                        product.id = element.val();
                        product.qty = 0;
                        element.closest('[data-role=row]').find('[data-column]').each(function(index, element) {
                            product[$(element).data('column')] = $.trim($(element).text());
                        });
                        selectedProductList[product.id] = product;
                    } else {
                        delete selectedProductList[element.val()];
                    }
                }, this)
            );

            var gridPopup = $(this.options.gridPopup).data('gridObject');

            $('[data-role=add-product]').on('click', function(event) {
                event.preventDefault();
                popup.dialog('open');
                gridPopup.reload();
                selectedProductList = {};
            });

            $('#' + gridPopup.containerId)
                .on('gridajaxsettings', function(event, ajaxSettings) {
                    var ids = widget.$grid.find('[data-role=id]').map(function(index, element) {
                        return $(element).val();
                    }).toArray();
                    ajaxSettings.data.filter = $.extend(ajaxSettings.data.filter || {}, {'entity_id': ids});
                })
                .on('gridajax', function(event, ajaxRequest) {
                    ajaxRequest.done(function() {
                        popup.find('[data-role=row] [data-column=entity_id] input')
                            .each(function(index, element) {
                                var $element = $(element);
                                $element.prop('checked', !!selectedProductList[$element.val()]);
                            });
                    });
                });
        },

        /**
         * Show or hide message
         * @private
         */
        _updateGridVisibility: function () {
            var showGrid = this.element.find('[data-role=id]').length > 0;
            this.element.find('.grid-container').toggle(showGrid);
            this.element.find('.no-products-message').toggle(!showGrid);
        }
    });
    
    return $.mage.groupedProduct;
});