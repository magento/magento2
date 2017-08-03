<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Stock\Item;

use Magento\CatalogInventory\Api\Data\StockItemCollectionInterface;
use Magento\Framework\Data\AbstractSearchResult;

/**
 * Class Collection
 * @package Magento\CatalogInventory\Model\ResourceModel\Stock\Item
 * @since 2.0.0
 */
class Collection extends AbstractSearchResult implements StockItemCollectionInterface
{
    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function init()
    {
        $this->setDataInterfaceName(\Magento\CatalogInventory\Api\Data\StockItemInterface::class);
    }
}
