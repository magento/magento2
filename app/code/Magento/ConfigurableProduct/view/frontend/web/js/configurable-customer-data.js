require([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';

    var selectors = {
        configurableWidget: 'mageConfigurable',
        formSelector: '#product_addtocart_form',
        productIdSelector: '#product_addtocart_form [name="product"]'
    },
    configurableWidget,
    productOptions,
    changedProductOptions,
    cartData = customerData.get('cart'),
    productId = $(selectors.productIdSelector).val(),
    updateConfigurableOptions,
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
            updateConfigurableOptions();
        }
    });
    $(selectors.formSelector).on('configurable.initialized', function () {
        setProductOptions(cartData());
        updateConfigurableOptions();
    });
});
