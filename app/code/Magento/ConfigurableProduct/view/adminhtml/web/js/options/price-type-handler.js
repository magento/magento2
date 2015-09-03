/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function ($) {
    'use strict';

    var isConfigurable;

    var checkPercentPriceTypeDomExist = function () {
        if ($('#product_options_container_top').length !== 0) {
            $('.opt-type > select').trigger('change');
        }
    };

    $('#super_config')
        .on('setIsConfigurable', function () {
            isConfigurable = true;
            checkPercentPriceTypeDomExist();
        }).on('setIsNotConfigurable', function () {
            isConfigurable = false;
            checkPercentPriceTypeDomExist();
        });

    $('#product-edit-form-tabs').on('change', '.opt-type > select', function () {
        var priceType = $('[data-attr="price-type"]'),
            optionPercentPriceType = priceType.find('option[value="percent"]');

        if (isConfigurable) {
            optionPercentPriceType.hide();
            optionPercentPriceType.parent().val() === 'percent' ? optionPercentPriceType.parent().val('fixed') : '';
        } else if (!isConfigurable) {
            optionPercentPriceType.show();
        }
    });
});