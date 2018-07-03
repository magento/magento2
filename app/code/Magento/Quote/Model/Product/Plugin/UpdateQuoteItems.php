<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @param \Magento\Catalog\Model\ResourceModel\Product $result
     * @param \Magento\Framework\Model\AbstractModel $product
     * @return \Magento\Catalog\Model\ResourceModel\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Magento\Catalog\Model\ResourceModel\Product $result,
        \Magento\Framework\Model\AbstractModel $product
    ) {
        $originalPrice = $product->getOrigData('price');
        $tierPriceChanged = $product->getData('tier_price_changed');
        if ((!empty($originalPrice) && ($originalPrice != $product->getPrice())) || $tierPriceChanged) {
            $this->resource->markQuotesRecollect($product->getId());
        }
        return $result;
    }
}
