<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Product\Plugin;

/**
 * BeforeSave plugin for product resource model to update quote items prices if product price is changed
 */
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
     * @param \Magento\Framework\Model\AbstractModel $product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Magento\Framework\Model\AbstractModel $product
    ) {
        $originalPrice = $product->getOrigData('price');
        if (!empty($originalPrice) && ($originalPrice != $product->getPrice())) {
            $this->resource->markQuotesRecollect($product->getId());
        }
    }
}
