<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface;
use Magento\Framework\Data\AbstractSearchResult;

/**
 * Class Collection
 * @package Magento\CatalogInventory\Model\ResourceModel\Stock\Status
 */
class Collection extends AbstractSearchResult implements StockStatusCollectionInterface
{
    /**
     * @inheritdoc
     */
    protected function init()
    {
        $this->setDataInterfaceName(\Magento\CatalogInventory\Api\Data\StockStatusInterface::class);
    }
}
