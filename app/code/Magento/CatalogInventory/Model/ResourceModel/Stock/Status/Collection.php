<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Stock\Status;

use Magento\CatalogInventory\Api\Data\StockStatusCollectionInterface;
use Magento\Framework\Data\AbstractSearchResult;

/**
 * Class Collection
 * @package Magento\CatalogInventory\Model\ResourceModel\Stock\Status
 * @since 2.0.0
 */
class Collection extends AbstractSearchResult implements StockStatusCollectionInterface
{
    /**
     * @inheritdoc
     * @since 2.0.0
     */
    protected function init()
    {
        $this->setDataInterfaceName(\Magento\CatalogInventory\Api\Data\StockStatusInterface::class);
    }
}
