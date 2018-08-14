<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Adds filters to the collection to help determine if product is available for sale.
     *
     * This method adds several additional checks for a children products availability.
     * Children products should be enabled and available in stock to be sold.
     * It also adds the specific flag to the collection to prevent the case
     * when filter already added and therefore may break the collection.
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
            $stockStatusResource->addStockDataToCollection($collection, true);
            $collection->setFlag($stockFlag, true);
        }

        return $collection;
    }
}
