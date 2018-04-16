/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_ConfigurableProduct/js/options-updater',
    'jquery/ui'
], function ($, Updater) {
    'use strict';

    $.widget('mage.selectSwatch', {
        options: {
            swatchOptions: null,
            selectors: {
                formSelector: '#product_addtocart_form',
                swatchSelector: '.swatch-opt'
            },
            swatchWidgetName: 'mageSwatchRenderer',
            widgetInitEvent: 'swatch.initialized',
            clickEventName: 'emulateClick'
        },

        /**
         * Widget initialisation.
         * Configurable product options updater listens to selected swatch options
         */
        _init: function () {
            var updater;

            updater = new Updater(this.options.widgetInitEvent, this.selectDefaultSwatchOptions.bind(this));
            updater.listen();
        },

        /**
         * Sets default configurable swatch attribute's selected
         */
        selectDefaultSwatchOptions: function () {
            var swatchWidget = $(this.options.selectors.swatchSelector).data(this.options.swatchWidgetName);

            if (!swatchWidget || !swatchWidget._EmulateSelectedByAttributeId) {
                return;
            }
            swatchWidget._EmulateSelectedByAttributeId(
                this.options.swatchOptions.defaultValues, this.options.clickEventName
            );
        }
    });

    return $.mage.selectSwatch;
});
