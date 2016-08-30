require([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    var selectors = {
        configurableWidget: 'mageConfigurable',
        swatchWidget: 'mageSwatchRenderer',
        formSelector: '#product_addtocart_form',
        swatchSelector: '.swatch-opt',
        productIdSelector: '#product_addtocart_form [name="product"]'
    },
    configurableWidget,
    swatchWidget,
    productOptions,
    tempProductOptions,
    cartData = customerData.get('cart'),
    productId = $(selectors.productIdSelector).val(),
    updateConfigurableOptions,
    updateSwatchOptions,
    setProductOptions;



    /**
    * Sets all configurable attribute's selected values
    */
    updateConfigurableOptions = function () {
        configurableWidget = $(selectors.formSelector).data(selectors.configurableWidget);
        
        if (!configurableWidget) {
            return;
        }
        configurableWidget.options.values = productOptions || {};
        configurableWidget._configureForValues();
    };

    /**
    * Sets all configurable swatch attribute's selected values
    */
    updateSwatchOptions = function () {
            swatchWidget = $(selectors.swatchSelector).data(selectors.swatchWidget);

            if (!swatchWidget || !swatchWidget._EmulateSelectedByAttributeId) {
                return;
            }
            swatchWidget._EmulateSelectedByAttributeId(productOptions);
    };

    /**
    * set productOptions according to cart data from customer-data
    *
    * @param {Object} data - cart data from customer-data
    * @returns {Boolean} - whether the new options differ from previous
    */
    setProductOptions = function (data) {
        if (!(data && data.items && data.items.length && productId)) {
            return;
        }
        tempProductOptions = data.items.find(function (item) {
            return item['product_id'] === productId;
        });
        tempProductOptions = tempProductOptions && tempProductOptions.options &&
            tempProductOptions.options.reduce(function (obj, val) {
                obj[val['option_id']] = val['option_value'];

                return obj;
        }, {});

        if (JSON.stringify(productOptions || {}) === JSON.stringify(tempProductOptions || {}) ) {
            return false;
        }

        productOptions = tempProductOptions;
        return true;
    };

    cartData.subscribe(function (updateCartData) {
        if (setProductOptions(updateCartData)) {
            updateConfigurableOptions();
            updateSwatchOptions();
        }
    });
    $(selectors.formSelector).on('configurable.initialized', function () {
            setProductOptions(cartData());
            updateConfigurableOptions();
    });
    $(selectors.formSelector).on('swatch.initialized', function () {
            setProductOptions(cartData());
            updateSwatchOptions();
    });
});
