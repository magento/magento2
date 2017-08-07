<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Product\Plugin;

/**
 * Class \Magento\Quote\Model\Product\Plugin\UpdateQuoteItems
 *
 * @since 2.2.0
 */
class UpdateQuoteItems
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote
     * @since 2.2.0
     */
    private $resource;

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote $resource
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function afterSave(
        \Magento\Catalog\Model\ResourceModel\Product $subject,
        \Magento\Catalog\Model\ResourceModel\Product $result,
        \Magento\Framework\Model\AbstractModel $product
    ) {
        $originalPrice = $product->getOrigData('price');
        if (!empty($originalPrice) && ($originalPrice != $product->getPrice())) {
            $this->resource->markQuotesRecollect($product->getId());
        }
        return $result;
    }
}
