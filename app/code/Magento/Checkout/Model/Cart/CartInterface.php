<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model\Cart;

use Magento\Quote\Model\Quote;

/**
 * Shopping cart interface
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @deprecated 2.1.0
 * @since 2.0.0
 */
interface CartInterface
{
    /**
     * Add product to shopping cart (quote)
     *
     * @param int|\Magento\Catalog\Model\Product $productInfo
     * @param array|float|int|\Magento\Framework\DataObject|null $requestInfo
     * @return $this
     * @since 2.0.0
     */
    public function addProduct($productInfo, $requestInfo = null);

    /**
     * Save cart
     *
     * @return $this
     * @abstract
     * @since 2.0.0
     */
    public function saveQuote();

    /**
     * Associate quote with the cart
     *
     * @param Quote $quote
     * @return $this
     * @abstract
     * @since 2.0.0
     */
    public function setQuote(Quote $quote);

    /**
     * Get quote object associated with cart
     *
     * @return Quote
     * @abstract
     * @since 2.0.0
     */
    public function getQuote();
}
