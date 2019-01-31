<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Stock;

use Magento\Framework\DB\GenericMapper;

/**
 * Class StockCriteriaMapper
 * @package Magento\CatalogInventory\Model\ResourceModel\Stock
 */
class StockCriteriaMapper extends GenericMapper
{
    /**
     * @inheritdoc
     */
    protected function init()
    {
        $this->initResource(\Magento\CatalogInventory\Model\ResourceModel\Stock::class);
    }
}
