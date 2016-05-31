/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Catalog/catalog/type-events',
    'collapsible',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'domReady!'
], function ($, productType) {
    'use strict';

    return {
        $block: null,
        hasVariations: null,
        configurationSectionMessageHandler: (function () {
            var title = $('[data-role="product-create-configuration-info"]'),
                buttons = $('[data-action="product-create-configuration-buttons"]'),
                newText = 'Configurations cannot be created for a standard product with downloadable files.' +
                ' To create configurations, first remove all downloadable files.',
                oldText = title.text();

            return function (change) {
                if (change) {
                    title.text(newText);
                    buttons.hide();
                } else {
                    title.text(oldText);
                    buttons.show();
                }
            };
        }()),

        /**
         * Set element disabled
         * @param {Object} $element - jquery instance element
         * @param {Bool} state
         * @param {Bool} triggerEvent
         * @private
         */
        _setElementDisabled: function ($element, state, triggerEvent) {
            if (!$element.is('[data-locked]')) {
                $element.prop('disabled', state);

                if (triggerEvent) {
                    $element.trigger('change');
                }
            }
        },

        /**
         * Show
         */
        show: function () {
            this.configurationSectionMessageHandler(false);
        },

        /**
         * Hide
         */
        hide: function () {
            this.configurationSectionMessageHandler(true);
        },

        /**
         * Bind all
         */
        bindAll: function () {
            $(document).on('changeConfigurableTypeProduct', function (event, isConfigurable) {
                $(document).trigger('setTypeProduct', isConfigurable ?
                    'configurable' :
                    productType.type.init === 'configurable' ? 'simple' : productType.type.init
                );
            });
            $(document).on('changeTypeProduct', this._initType.bind(this));
        },

        /**
         * Init type
         * @private
         */
        _initType: function () {

            /*var suggestContainer = $('#product-template-suggest-container .action-dropdown > .action-toggle');


            if (productType.type.current === 'configurable') {
                this._setElementDisabled(suggestContainer.addClass('disabled'), true);
                this._setElementDisabled($('#inventory_qty'), true);
                this._setElementDisabled($('#inventory_stock_availability'), false);
                this._setElementDisabled($('#qty'), true, true);
                this._setElementDisabled($('#quantity_and_stock_status'), false, false);
            } else {
                this._setElementDisabled(suggestContainer.removeClass('disabled'), false);
                this._setElementDisabled($('#inventory_qty'), false);
                this._setElementDisabled($('#inventory_stock_availability'), true);
                this._setElementDisabled($('#qty'), false, true);
            }
            */

            /*if (['simple', 'virtual', 'configurable'].indexOf(productType.type.current) < 0) {
                this.hide();
            } else {
                this.show();
            }*/

            this.show();
        },

        /**
         * Constructor component
         * @param {Object} data - this backend data
         */
        'Magento_ConfigurableProduct/js/configurable-type-handler': function (data) {
            this.$block = $(data.blockId + ' input[name="attributes[]"]');
            this.hasVariations = data.hasVariations;

            //advancedPricingHandler.init();
            //priceTypeHandler.init();

            /*if (productType.type.init === 'configurable' && !this.hasVariations) {
                $(document).trigger('setTypeProduct', 'simple');
            }*/
            $(document).trigger('setTypeProduct', 'simple');

            this.bindAll();
            this._initType();
        }
    };
});
