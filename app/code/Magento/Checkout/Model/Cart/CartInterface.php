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
<<<<<<< HEAD
 * @deprecated 100.1.0 Use \Magento\Quote\Model\Quote instead
=======
 * @deprecated 100.1.0 Use \Magento\Quote\Api\Data\CartInterface instead
 * @see \Magento\Quote\Api\Data\CartInterface
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
interface CartInterface
{
    /**
     * Add product to shopping cart (quote)
     *
     * @param int|\Magento\Catalog\Model\Product $productInfo
     * @param array|float|int|\Magento\Framework\DataObject|null $requestInfo
     * @return $this
     */
    public function addProduct($productInfo, $requestInfo = null);

    /**
     * Save cart
     *
     * @return $this
     * @abstract
     */
    public function saveQuote();

    /**
     * Associate quote with the cart
     *
     * @param Quote $quote
     * @return $this
     * @abstract
     */
    public function setQuote(Quote $quote);

    /**
     * Get quote object associated with cart
     *
     * @return Quote
     * @abstract
     */
    public function getQuote();
}
