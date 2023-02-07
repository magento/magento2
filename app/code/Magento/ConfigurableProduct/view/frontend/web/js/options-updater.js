define([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data',
    'domReady!'
], function ($, _, customerData) {
    'use strict';

    var selectors = {
        formSelector: '#product_addtocart_form',
        productIdSelector: '#product_addtocart_form [name="product"]',
        itemIdSelector: '#product_addtocart_form [name="item"]'
    },
    cartData = customerData.get('cart'),
    productId = $(selectors.productIdSelector).val(),
    itemId = $(selectors.itemIdSelector).val(),

    /**
    * set productOptions according to cart data from customer-data
    *
    * @param {Object} data - cart data from customer-data
    * @returns {Boolean} - whether the new options differ from previous
    */
    setProductOptions = function (data) {
        var changedProductOptions;

        if (!(data && data.items && data.items.length && productId)) {
            return false;
        }
        changedProductOptions = _.find(data.items, function (item) {
            if (item['item_id'] === itemId) {
                return item['product_id'] === productId;
            }
        });
        changedProductOptions = changedProductOptions && changedProductOptions.options &&
            changedProductOptions.options.reduce(function (obj, val) {
                obj[val['option_id']] = val['option_value'];

                return obj;
            }, {});

        if (JSON.stringify(this.productOptions || {}) === JSON.stringify(changedProductOptions || {})) {
            return false;
        }

        this.productOptions = changedProductOptions;

        return true;
    },

    /**
    * Listens to update of cart data or options initialization and update selected option according to customer data
    *
    */
    listen = function () {
        cartData.subscribe(function (updateCartData) {
            if (this.setProductOptions(updateCartData)) {
                this.updateOptions();
            }
        }.bind(this));
        $(selectors.formSelector).on(this.eventName, function () {
            this.setProductOptions(cartData());
            this.updateOptions();
        }.bind(this));
    },

    /**
    * Updater constructor function
    *
    */
    Updater = function (eventName, updateOptionsCallback) {
        if (this instanceof Updater) {
            this.eventName = eventName;
            this.updateOptions = updateOptionsCallback;
            this.productOptions = {};
        }
    };

    Updater.prototype.setProductOptions = setProductOptions;
    Updater.prototype.listen = listen;

    return Updater;
});
