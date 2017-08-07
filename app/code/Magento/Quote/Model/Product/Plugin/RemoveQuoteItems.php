<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Product\Plugin;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;

/**
 * Class \Magento\Quote\Model\Product\Plugin\RemoveQuoteItems
 *
 * @since 2.1.3
 */
class RemoveQuoteItems
{
    /**
     * @var \Magento\Quote\Model\Product\QuoteItemsCleanerInterface
     * @since 2.1.3
     */
    private $quoteItemsCleaner;

    /**
     * @param \Magento\Quote\Model\Product\QuoteItemsCleanerInterface $quoteItemsCleaner
     * @since 2.1.3
     */
    public function __construct(\Magento\Quote\Model\Product\QuoteItemsCleanerInterface $quoteItemsCleaner)
    {
        $this->quoteItemsCleaner = $quoteItemsCleaner;
    }

    /**
     * @param ProductResource $subject
     * @param ProductResource $result
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return ProductResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function afterDelete(
        ProductResource $subject,
        ProductResource $result,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        $this->quoteItemsCleaner->execute($product);
        return $result;
    }
}
