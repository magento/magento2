/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_ConfigurableProduct/js/advanced-pricing-handler',
    'Magento_ConfigurableProduct/js/options/price-type-handler',
    'Magento_Catalog/catalog/type-events',
    'collapsible',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'domReady!'
], function($, advancedPricingHandler, priceTypeHandler, typeHandler){
    'use strict';

    return {
        $block: null,
        hasVariations: null,
        configurationSectionMessageHandler: (function() {
            var title = $('[data-role="product-create-configuration-info"]');
            var buttons = $('[data-action="product-create-configuration-buttons"]');
            var newText = 'Configurations cannot be created for a standard product with downloadable files.' +
                ' To create configurations, first remove all downloadable files.';
            var oldText = title.text();
            return function (change) {
                if (change) {
                    title.text(newText);
                    buttons.hide();
                } else {
                    title.text(oldText);
                    buttons.show();
                }
            }.bind(this);
        }()),
        _setElementDisabled: function ($element, state, triggerEvent) {
            if (!$element.is('[data-locked]')) {
                $element.prop('disabled', state);

                if (triggerEvent) {
                    $element.trigger('change');
                }
            }
        },
        show: function () {
            this.configurationSectionMessageHandler(false);
        },
        hide: function () {
            this.configurationSectionMessageHandler(true);
        },
        bindAll: function () {
            $(document).on('changeConfigurableTypeProduct', function (event, isConfigurable) {
                $(document).trigger('setTypeProduct', isConfigurable ? 'configurable' : null);
            }.bind(this));
            $(document).on('changeTypeProduct', function (event, controllers) {
                var suggestContainer = $('#product-template-suggest-container .action-dropdown > .action-toggle');
                if (controllers.type.current === 'configurable') {
                    suggestContainer.addClass('disabled').prop('disabled', true);
                    this.$block.prop('disabled', false);
                    $('#inventory_qty').prop('disabled', true);
                    $('#inventory_stock_availability').removeProp('disabled');
                    this._setElementDisabled($('#qty'), true, true);
                    this._setElementDisabled($('#quantity_and_stock_status'), false, false);
                } else {
                    suggestContainer.removeClass('disabled').removeProp('disabled');
                    this.$block.prop('disabled', true);
                    $('#inventory_qty').removeProp('disabled');
                    $('#inventory_stock_availability').prop('disabled', true);
                    this._setElementDisabled($('#quantity_and_stock_status'), true, false);
                    this._setElementDisabled($('#qty'), false, true);
                }
            }.bind(this));
        },
        'Magento_ConfigurableProduct/js/configurable-type-handler': function (inData) {
            this.$block = $(inData.blockId + ' input[name="attributes[]"]');
            this.hasVariations = inData.hasVariations;

            advancedPricingHandler.init();
            priceTypeHandler.init();

            this.bindAll();
        }
    };
});
