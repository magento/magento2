<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Product\Plugin;

class UpdateQuoteItems
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     */
    private $resource;

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote $resource
     */
    public function __construct(
        \Magento\Quote\Model\ResourceModel\Quote $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Model\AbstractModel $product
     * @return \Magento\Catalog\Model\ResourceModel\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Closure $proceed,
        \Magento\Framework\Model\AbstractModel $product
    ) {
        $result = $proceed($product);
        $originalPrice = $product->getOrigData('price');
        if (!empty($originalPrice) && ($originalPrice != $product->getPrice())) {
            $this->resource->markQuotesRecollect($product->getId());
        }
        return $result;
    }
}
