<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\QuoteManagement;

use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

/**
 * Fill quote with products for instant purchase.
 *
 * @api May be used for pluginization.
 */
class QuoteFilling
{
    /**
     * Adds products to quote according to request.
     *
     * @param Quote $quote
     * @param Product $product
     * @param array $productRequest
     * @return Quote
     * @throws LocalizedException if product can not be added to quote.
     */
    public function fillQuote(
        Quote $quote,
        Product $product,
        array $productRequest
    ): Quote {
        $normalizedProductRequest = array_merge(
            ['qty' => 1],
            $productRequest
        );
        $result = $quote->addProduct(
            $product,
            new DataObject($normalizedProductRequest)
        );

        if (is_string($result)) {
            throw new LocalizedException(__($result));
        }
        return $quote;
    }
}
