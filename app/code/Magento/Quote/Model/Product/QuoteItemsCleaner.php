<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
