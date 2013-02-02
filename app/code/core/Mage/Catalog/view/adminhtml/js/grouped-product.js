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
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function($) {
    $.widget('mage.groupedProduct', {
        _create: function () {
            this.$grid = this.element.find('#grouped_grid');
            this.$popup = this.element.find('#grouped_grid_popup');

            this._bindDialog();
            this._bindEventHandlers();
            if (!$.isArray(this.options.gridData) || this.options.gridData.length) {
                this._initGridWithData(this.options.gridData);
            }
            this._displayGridRow(this.options.associatedProductIds);
            this._updatePopupGrid();
            this._updateHiddenField(this.options.associatedProductIds);
            this._sortGridByPosition();
        },

        _bindDialog: function () {
            var widget = this;
            $('#grouped-product-popup').dialog({
                title: 'Add Products to Group',
                autoOpen: false,
                minWidth: 980,
                modal: true,
                resizable: true,
                buttons: [{
                    id: 'grouped-product-dialog-cancel-button',
                    text: 'Cancel',
                    click: function () {
                        widget._updatePopupGrid();
                        $(this).dialog('close');
                    }
                }, {
                    id: 'grouped-product-dialog-apply-button',
                    text: 'Apply Changes',
                    'class': 'add',
                    click: function () {
                        var ids = widget._getSelectedIds();
                        widget._displayGridRow(ids);
                        widget._updateHiddenField(ids);
                        $(this).dialog('close');
                    }
                }]
            });
        },

        _bindEventHandlers: function () {
            var widget = this;
            $('#grouped-add-products').on('click', function () {
                $('#grouped-product-popup').dialog('open');
                return false;
            });
            this.$grid.on('click', '.product-delete button', function (event) {
                $(this).closest('tr').hide().addClass('ignore-validate');
                widget._updatePopupGrid();
                widget._updateHiddenField(widget._getSelectedIds());
                widget._updateGridVisibility();
            });
            this.$grid.on('change keyup', 'input[type="text"]', function (event) {
                widget._updateHiddenField(widget._getSelectedIds());
            });
            this.options.grid.rowClickCallback = function () {};
            this.options.gridPopup.rowClickCallback = function (grid, event) {
                event.stopPropagation();
                if (!this.rows || !this.rows.length) {
                    return;
                }
                $(event.target).parent().find('td.selected-products input[type="checkbox"]').click();
                return false;
            };
        },

        updateRowsPositions: function () {
            $.each(this.$grid.find('input[name="position"]'), function (index) {
                $(this).val(index);
            });
            this._updateHiddenField(this._getSelectedIds());
        },

        _updateHiddenField: function (ids) {
            var gridData = {}, widget = this;
            $.each(this.$grid.find('input[name="entity_id"]'), function () {
                var $idContainer = $(this),
                    inArray = $.inArray($idContainer.val(), ids) !== -1;
                if (inArray) {
                    var data = {};
                    $.each(widget.options.fieldsToSave, function (k, v) {
                        data[v] = $idContainer.closest('tr').find('input[name="' + v + '"]').val();
                    });
                    gridData[$idContainer.val()] = data;
                }
            });
            widget.options.$hiddenInput.val(JSON.stringify(gridData));
        },

        _displayGridRow: function (ids) {
            var displayedRows = 0;
            $.each(this.$grid.find('input[name="entity_id"]'), function () {
                var $idContainer = $(this),
                    inArray = $.inArray($idContainer.val(), ids) !== -1;
                $idContainer.closest('tr').toggle(inArray).toggleClass('ignore-validate', !inArray);
                if (inArray) {
                    displayedRows++;
                }
            });
            this._updateGridVisibility(displayedRows);
        },

        _initGridWithData: function (gridData) {
            $.each(this.$grid.find('input[name="entity_id"]'), function () {
                var $idContainer = $(this),
                    id = $idContainer.val();
                if (!gridData[id]) {
                    return true;
                }
                $.each(gridData[id], function (fieldName, data) {
                    $idContainer.closest('tr').find('input[name="' + fieldName + '"]').val(data);
                });
            });
        },

        _getSelectedIds: function () {
            var ids = [];
            $.each(this.$popup.find('.selected-products input[type="checkbox"]:checked'),
                function () {
                    ids.push($(this).val());
                }
            );
            return ids;
        },

        _updatePopupGrid: function () {
            var $popup = this.$popup;
            $.each(this.$grid.find('input[name="entity_id"]'), function () {
                var id = $(this).val();
                $popup.find('input[type=checkbox][value="' + id + '"]')
                    .prop({checked: !$(this).closest('tr').hasClass('ignore-validate')});
            });
        },

        _sortGridByPosition: function () {
            var rows = this.$grid.find('tbody tr');
            rows.sort(function (a, b) {
                var valueA = $(a).find('input[name="position"]').val(),
                    valueB = $(b).find('input[name="position"]').val();
                return (valueA < valueB) ? -1 : (valueA > valueB) ? 1 : 0;
            });
            this.$grid.find('tbody').html(rows);
        },

        _updateGridVisibility: function (showGrid) {
            showGrid = showGrid || this.element.find('#grouped_grid_table tbody tr:visible').length > 0;
            this.element.find('.grid-wrapper').toggle(showGrid);
            this.element.find('.no-products-message').toggle(!showGrid);
        }
    });
})(jQuery);
