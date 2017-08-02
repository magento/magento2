<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Product;

/**
 * Class \Magento\Quote\Model\Product\QuoteItemsCleaner
 *
 * @since 2.2.0
 */
class QuoteItemsCleaner implements \Magento\Quote\Model\Product\QuoteItemsCleanerInterface
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item
     * @since 2.2.0
     */
    private $itemResource;

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item $itemResource
     * @since 2.2.0
     */
    public function __construct(\Magento\Quote\Model\ResourceModel\Quote\Item $itemResource)
    {
        $this->itemResource = $itemResource;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $this->itemResource->getConnection()->delete(
            $this->itemResource->getMainTable(),
            'product_id = ' . $product->getId()
        );
    }
}
