require([
    'jquery',
    'Magento_Customer/js/customer-data',
    'underscore',
    'domReady!'
], function ($, customerData, _) {
    'use strict';

    var selectors = {
        qtySelector: '#product_addtocart_form [name="qty"]',
        productIdSelector: '#product_addtocart_form [name="product"]',
        itemIdSelector: '#product_addtocart_form [name="item"]'
    },
    cartData = customerData.get('cart'),
    productId = $(selectors.productIdSelector).val(),
    itemId = $(selectors.itemIdSelector).val(),
    productQty,
    productQtyInput,

    /**
    * Updates product's qty input value according to actual data
    */
    updateQty = function () {

        if (productQty || productQty === 0) {
            productQtyInput = productQtyInput || $(selectors.qtySelector);

            if (productQtyInput && productQty.toString() !== productQtyInput.val()) {
                productQtyInput.val(productQty);
            }
        }
    },

    /**
    * Sets productQty according to cart data from customer-data
    *
    * @param {Object} data - cart data from customer-data
    */
    setProductQty = function (data) {
        var product;

        if (!(data && data.items && data.items.length && productId)) {
            return;
        }
        product = _.find(data.items, function (item) {
            if (item['item_id'] === itemId) {
                return item['product_id'] === productId ||
                    item['item_id'] === productId;
            }
        });

        if (!product) {
            return;
        }
        productQty = product.qty;
    };

    cartData.subscribe(function (updateCartData) {
        setProductQty(updateCartData);
        updateQty();
    });

    setProductQty(cartData());
    updateQty();
});
