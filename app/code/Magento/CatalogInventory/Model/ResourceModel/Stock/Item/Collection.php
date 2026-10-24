<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Stock\Item;

use Magento\CatalogInventory\Api\Data\StockItemCollectionInterface;
use Magento\Framework\Data\AbstractSearchResult;

/**
 * Class Collection
 * @package Magento\CatalogInventory\Model\ResourceModel\Stock\Item
 */
class Collection extends AbstractSearchResult implements StockItemCollectionInterface
{
    /**
     * @inheritdoc
     */
    protected function init()
    {
        $this->setDataInterfaceName(\Magento\CatalogInventory\Api\Data\StockItemInterface::class);
    }
}
