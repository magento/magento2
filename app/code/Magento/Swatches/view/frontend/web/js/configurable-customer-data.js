require([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    var selectors = {
        swatchWidget: 'mageSwatchRenderer',
        formSelector: '#product_addtocart_form',
        swatchSelector: '.swatch-opt',
        productIdSelector: '#product_addtocart_form [name="product"]'
    },
    swatchWidget,
    productOptions,
    changedProductOptions,
    cartData = customerData.get('cart'),
    productId = $(selectors.productIdSelector).val(),
    updateSwatchOptions,
    setProductOptions;

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
            return false;
        }
        changedProductOptions = data.items.find(function (item) {
            return item['product_id'] === productId;
        });
        changedProductOptions = changedProductOptions && changedProductOptions.options &&
            changedProductOptions.options.reduce(function (obj, val) {
                obj[val['option_id']] = val['option_value'];

                return obj;
            }, {});

        if (JSON.stringify(productOptions || {}) === JSON.stringify(changedProductOptions || {})) {
            return false;
        }

        productOptions = changedProductOptions;

        return true;
    };

    cartData.subscribe(function (updateCartData) {
        if (setProductOptions(updateCartData)) {
            updateSwatchOptions();
        }
    });
    $(selectors.formSelector).on('swatch.initialized', function () {
        setProductOptions(cartData());
        updateSwatchOptions();
    });
});
