<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Product;

class QuoteItemsCleaner implements \Magento\Quote\Model\Product\QuoteItemsCleanerInterface
{
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item
     */
    private $itemResource;

    /**
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item $itemResource
     */
    public function __construct(\Magento\Quote\Model\ResourceModel\Quote\Item $itemResource)
    {
        $this->itemResource = $itemResource;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $this->itemResource->getConnection()->delete(
            $this->itemResource->getMainTable(),
            'product_id = ' . $product->getId()
        );
    }
}
