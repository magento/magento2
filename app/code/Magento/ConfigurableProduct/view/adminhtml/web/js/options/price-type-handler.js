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

    var priceTypeHandler = {
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
        }
    };

    $('[data-form=edit-product]')
        .on('change_configurable_type', function (event, isConfigurable) {
            priceTypeHandler.isConfigurable = isConfigurable;
            priceTypeHandler.checkPercentPriceTypeDomExist();
        });

    $('#product-edit-form-tabs').on('change', '.opt-type > select', function () {
        var priceType = $('[data-attr="price-type"]'),
            optionPercentPriceType = priceType.find('option[value="percent"]');

        if (priceTypeHandler.isConfigurable) {
            priceTypeHandler.showWarning();
            optionPercentPriceType.hide();
            optionPercentPriceType.parent().val() === 'percent' ? optionPercentPriceType.parent().val('fixed') : '';
        } else {
            optionPercentPriceType.show();
            priceTypeHandler.hideWarning();
        }
    });
    return priceTypeHandler;
});