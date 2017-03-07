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
 * Class SalableProcessor
 * Add is_in_stock filter to products collection and cashes it
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
