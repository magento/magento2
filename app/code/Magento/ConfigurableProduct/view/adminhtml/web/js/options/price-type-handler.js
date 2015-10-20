/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'notification',
    'mage/translate'
], function ($) {
    'use strict';

    return {
        isConfigurable: false,
        messageInited: false,
        messageSelector: '[data-role=product-custom-options-content]',
        isPercentPriceTypeExist: function () {
            var productOptionsContainer = $('#product_options_container_top');

            return !!productOptionsContainer.length;
        },
        showWarning: function () {
            if (!this.messageInited) {
                $(this.messageSelector).notification();
                this.messageInited = true;
            }
            this.hideWarning();
            $(this.messageSelector).notification('add', {
                message: $.mage.__('We can\'t save custom-defined options with price type "percent" for ' +
                    'configurable product.'),
                error: true,
                messageContainer: this.messageSelector
            });
        },
        hideWarning: function () {
            $(this.messageSelector).notification('clear');
        },
        init: function () {
            $('[data-form=edit-product]')
                .on('change_configurable_type', function (event, isConfigurable) {
                    this.isConfigurable = isConfigurable;
                    if (this.isPercentPriceTypeExist()) {
                        this.percentPriceTypeHandler();
                    }
                }.bind(this));

            $('#product-edit-form-tabs').on('change', '.opt-type > select', function () {
                var selected = $('.opt-type > select :selected'),
                    optGroup = selected.parent().attr('label');

                if (optGroup === 'Select') {
                    $('#product-edit-form-tabs').on(
                        'click',
                        '[data-ui-id="admin-product-options-options-box-select-option-type-add-select-row-button"]',
                        function () {
                            this.percentPriceTypeHandler();
                        }.bind(this)
                    );
                } else {
                    this.percentPriceTypeHandler();
                }
            }.bind(this));
        },
        percentPriceTypeHandler: function () {
            var priceType = $('[data-attr="price-type"]'),
                optionPercentPriceType = priceType.find('option[value="percent"]');

            if (this.isConfigurable) {
                this.showWarning();
                optionPercentPriceType.hide();
                optionPercentPriceType.parent().val() === 'percent' ? optionPercentPriceType.parent().val('fixed') : '';
            } else {
                $(this.messageSelector).notification();
                optionPercentPriceType.show();
                this.hideWarning();
            }
        }
    };
});