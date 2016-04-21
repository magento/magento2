/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*
define([
    'jquery',
    'Magento_Catalog/catalog/type-events',
    'notification',
    'mage/translate'
], function ($, productType) {
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
                message: $.mage.__('Custom options with price type "percent" is not available for ' +
                    'configurable product.'),
                error: false,
                messageContainer: this.messageSelector
            });
        },
        hideWarning: function () {
            $(this.messageSelector).notification('clear');
        },
        init: function () {
            $(document).on('changeTypeProduct', this._initType.bind(this));

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

            this._initType();
        },
        _initType: function () {
            this.isConfigurable = productType.type.current === 'configurable';
            if (this.isPercentPriceTypeExist()) {
                this.percentPriceTypeHandler();
            }
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
*/