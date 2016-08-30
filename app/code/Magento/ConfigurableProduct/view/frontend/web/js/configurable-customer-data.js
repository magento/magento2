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
    cartData = customerData.get('cart'),
    productId = $(selectors.productIdSelector).val(),
    selectOption,
    updateConfigurableOptions,
    updateSwatchOptions,
    setProductOptions;

    /*
    if (!$(selectors.formSelector).data(selectors.configurableWidget)) {
        return;
    }*/

    /**
    * Sets specific configurable attribute's selected value
    *
    * @param {HTMLElement} elem - configurable attribute
    * @param {String} value - configurable attribute's selected value
    */
    selectOption = function (elem, value) {
        elem = $(elem);

        if (value && elem.val() !== value) {
            elem.val(value);
        }
    };

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

/*
        $(selectors.superSelector).each(function () {
            currentAttributeId = (selectors.attributeIdRegex.exec((this.attributes[selectors.attributeIdSelector] || {}).value) || [])[1];

            if (productOptions && currentAttributeId) {
                selectOption(this, productOptions[currentAttributeId]);
            }
        });*/
    };

    /**
    * Sets all configurable swatch attribute's selected values
    */
    updateSwatchOptions = function () {
        if (!productOptions) {
            setProductOptions(cartData());
        }
        if (!productOptions) {
            return;
        }

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
    */
    setProductOptions = function (data) {
        if (!(data && data.items && data.items.length && productId)) {
            return;
        }
        productOptions = data.items.find(function (item) {
                return item['product_id'] === productId;
        });
        productOptions = productOptions && productOptions.options &&
            productOptions.options.reduce(function (obj, val) {
                obj[val.attributeId] = val.attributeValue;

                return obj;
        }, {});
    }

    cartData.subscribe(function (updateCartData) {
        setProductOptions(updateCartData);
        updateConfigurableOptions();
        updateSwatchOptions();
    });
    $(selectors.formSelector).on('configurable.initialized', function () {
        setProductOptions(cartData());
        updateConfigurableOptions();
    });
    $(selectors.formSelector).on('swatch.initialized', function () {
        updateSwatchOptions();
    });
});
