<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Product\Plugin;

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
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product $result
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Model\ResourceModel\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDelete(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Magento\Catalog\Model\ResourceModel\Product $result,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        $this->quoteItemsCleaner->execute($product);
        return $result;
    }
}
