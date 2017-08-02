<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Stock;

use Magento\CatalogInventory\Api\Data\StockCollectionInterface;
use Magento\Framework\Data\AbstractSearchResult;

/**
 * Class Collection
 * @package Magento\CatalogInventory\Model\ResourceModel\Stock
 * @since 2.0.0
 */
class Collection extends AbstractSearchResult implements StockCollectionInterface
{
    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function init()
    {
        $this->setDataInterfaceName(\Magento\CatalogInventory\Api\Data\StockInterface::class);
    }
}
