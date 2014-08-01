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
define([
    "jquery",
    "jquery/ui",
    "jquery/template",
    "mage/adminhtml/form"
], function($){

    $.widget('mage.fptAttribute', {
        _create: function () {
            var widget = this;
            this._initOptionItem();
            if ($(this.options.bundlePriceType).val() === '0') {
                this.element.hide();
            }
            $.each(this.options.itemsData, function () {
                widget.addItem(this);
            })
        },
        _initOptionItem: function () {
            var widget = this;
            this._on({
                //Add new tax item
                'click [data-action=add-fpt-item]': function (event) {
                    this.addItem(event);
                },
                //Delete tax item
                'click [data-action=delete-fpt-item]': function (event) {
                    var parent = $(event.target).closest('[data-role="fpt-item-row"]');
                    parent.find('[data-role="delete-fpt-item"]').val(1);
                    parent.addClass('ignore-validate').hide();
                },
                //Change tax item country/state
                'change [data-role="select-country"]': function (event, data) {
                    var currentElement = event.target || event.srcElement || event.currentTarget,
                        parentElement  = $(currentElement).closest('[data-role="fpt-item-row"]');
                    data = data || {};
                    var updater = new RegionUpdater(
                        parentElement.find('[data-role="select-country"]').attr('id'), null,
                        parentElement.find('[data-role="select-state"]').attr('id'),
                        widget.options.region, 'disable', true
                    );
                    updater.update();
                    //set selected state value if set
                    if (data.state) {
                        parentElement.find('[data-role="select-state"]').val(data.state);
                    }
                }
            });
            $(this.options.bundlePriceType).on('change', function (event) {
                var attributeItems = widget.element.find('[data-role="delete-fpt-item"]');
                if ($(event.target).val() === '0') {
                    widget.element.hide();
                    attributeItems.each(function () {
                        $(this).val(1);
                    })
                } else {
                    widget.element.show();
                    attributeItems.each(function () {
                        if ($(this).closest('[data-role="fpt-item-row"]').is(':visible')) {
                            $(this).val(0);
                        }
                    })
                }
            })
        },
        //Add custom option
        addItem: function (event) {
            var data = {},
                currentElement = event.target || event.srcElement || event.currentTarget;
            if (typeof currentElement !== 'undefined') {
                data.website_id = 0;
            } else {
                data = event;
            }
            data.index = this.element.find('[data-role="fpt-item-row"]').length;
            this.element.find('[data-role="row-template"]').tmpl(data)
                .appendTo(this.element.find('[data-role="fpt-item-container"]'));
            //set selected website_id value if set
            if (data.website_id) {
                this.element.find('[data-role="select-website"][id$="_' + data.index + '_website"]')
                    .val(data.website_id);
            }
            //set selected country value if set
            if (data.country) {
                this.element.find('[data-role="select-country"][id$="_' + data.index + '_country"]')
                    .val(data.country).trigger('change', data);
            }
        }
    });

});