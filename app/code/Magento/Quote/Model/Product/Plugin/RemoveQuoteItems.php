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
     * @param \Closure $proceed
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * TODO: reimplement with after plugin
     */
    public function aroundDelete(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Closure $proceed,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        $result = $proceed($product);
        $this->quoteItemsCleaner->execute($product);
        return $result;
    }
}
