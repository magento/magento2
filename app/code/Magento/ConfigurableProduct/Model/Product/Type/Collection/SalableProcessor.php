<?php
/**
 * Copyright © 2013-2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Collection;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;

/**
 * This class is responsible for adding additional filters for products collection
 * to check if the product from this collection available to buy.
 */
class SalableProcessor
{
    /**
     * @var StatusFactory
     */
    private $stockStatusFactory;

    /**
     * @param StatusFactory $stockStatusFactory
     */
    public function __construct(StatusFactory $stockStatusFactory)
    {
        $this->stockStatusFactory = $stockStatusFactory;
    }

    /**
     * Adds filtering collection by attribute status to filter only enabled products from product collection.
     * Joins stock status index table to filter only in stock products.
     *
     * Adds is_in_stock filter to products collection and cashes it.
     *
     * @param Collection $collection
     * @return Collection
     */
    public function process(Collection $collection)
    {
        $collection->addAttributeToFilter(
            ProductInterface::STATUS,
            Status::STATUS_ENABLED
        );

        $stockFlag = 'has_stock_status_filter';
        if (!$collection->hasFlag($stockFlag)) {
            $stockStatusResource = $this->stockStatusFactory->create();
            $stockStatusResource->addIsInStockFilterToCollection($collection);
            $collection->setFlag($stockFlag, true);
        }

        return $collection;
    }
}
