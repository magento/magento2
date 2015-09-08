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
        checkPercentPriceTypeDomExist: function () {
            if ($('#product_options_container_top').length !== 0) {
                $('.opt-type > select').trigger('change');
            }
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
                    this.checkPercentPriceTypeDomExist();
                }.bind(this));

            $('#product-edit-form-tabs').on('change', '.opt-type > select', function () {
                var priceType = $('[data-attr="price-type"]'),
                    optionPercentPriceType = priceType.find('option[value="percent"]');

                if (this.isConfigurable) {
                    this.showWarning();
                    optionPercentPriceType.hide();
                    optionPercentPriceType.parent().val() === 'percent' ? optionPercentPriceType.parent().val('fixed') : '';
                } else {
                    optionPercentPriceType.show();
                    this.hideWarning();
                }
            }.bind(this));
        }
    };
});