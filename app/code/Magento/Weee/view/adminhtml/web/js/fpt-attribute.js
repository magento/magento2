/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'mage/adminhtml/form'
], function ($, mageTemplate) {
    'use strict';

    $.widget('mage.fptAttribute', {
        _create: function () {
            var widget = this;

            this.rowTmpl = mageTemplate(this.element.find('[data-role="row-template"]').html());

            this._initOptionItem();

            if ($(this.options.bundlePriceType).val() === '0') {
                this.element.hide();
            }

            $.each(this.options.itemsData, function () {
                widget.addItem(this);
            });
        },
        _initOptionItem: function () {
            var widget = this,
                isOriginalRequired = $(widget.element).hasClass('required');

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
                        parentElement = $(currentElement).closest('[data-role="fpt-item-row"]');

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
                    if (!isOriginalRequired && $(widget.element).hasClass('required')) {
                        $(widget.element).removeClass('required');
                    }
                }
            });

            $(this.options.bundlePriceType).on('change', function (event) {
                var attributeItems = widget.element.find('[data-role="delete-fpt-item"]');

                if ($(event.target).val() === '0') {
                    widget.element.hide();
                    attributeItems.each(function () {
                        $(this).val(1);
                    });
                } else {
                    widget.element.show();
                    attributeItems.each(function () {
                        if ($(this).closest('[data-role="fpt-item-row"]').is(':visible')) {
                            $(this).val(0);
                        }
                    });
                }
            });
        },
        //Add custom option
        addItem: function (event) {
            var data = {},
                currentElement = event.target || event.srcElement || event.currentTarget,
                tmpl;

            if (typeof currentElement !== 'undefined') {
                data.website_id = 0;
            } else {
                data = event;
            }

            data.index = this.element.find('[data-role="fpt-item-row"]').length;

            tmpl = this.rowTmpl({
                data: data
            });

            $(tmpl).appendTo(this.element.find('[data-role="fpt-item-container"]'));

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

    return $.mage.fptAttribute;
});
