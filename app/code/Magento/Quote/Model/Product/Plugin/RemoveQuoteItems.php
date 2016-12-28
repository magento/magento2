<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Product\Plugin;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;

class RemoveQuoteItems
{
    /**
     * @var \Magento\Quote\Model\Product\QuoteItemsCleanerInterface
     */
    private $quoteItemsCleaner;

    /**
     * @param \Magento\Quote\Model\Product\QuoteItemsCleanerInterface $quoteItemsCleaner
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
