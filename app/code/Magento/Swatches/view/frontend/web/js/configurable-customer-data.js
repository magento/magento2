require([
    'jquery',
    'Magento_ConfigurableProduct/js/options-updater'
], function ($, Updater) {
    'use strict';

    var selectors = {
            formSelector: '#product_addtocart_form',
            swatchSelector: '.swatch-opt'
        },
        swatchWidgetName = 'mageSwatchRenderer',
        widgetInitEvent = 'swatch.initialized',

    /**
    * Sets all configurable swatch attribute's selected values
    */
    updateSwatchOptions = function () {
        var swatchWidget = $(selectors.swatchSelector).data(swatchWidgetName);

        document.body.setAttribute('data-debug', swatchWidget ? 'swatchWidget is not null' : 'swatchWidget is  null')

        if (!swatchWidget || !swatchWidget._EmulateSelectedByAttributeId) {
            return;
        }
        swatchWidget._EmulateSelectedByAttributeId(this.productOptions);
    },
    updater = new Updater(widgetInitEvent, updateSwatchOptions);

    updater.listen();
});
