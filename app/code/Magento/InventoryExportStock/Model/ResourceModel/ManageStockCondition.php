<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model\ResourceModel;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\DB\Select;

/**
 * Class ManageStockCondition
 */
class ManageStockCondition
{
    /**
     * @var StockConfigurationInterface
     */
    private $configuration;

    /**
     * @param StockConfigurationInterface $configuration
     */
    public function __construct(StockConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Provide product manage stock condition for db select
     *
     * @param Select $select
     * @return string
     */
    public function execute(Select $select): string
    {
        $globalManageStock = (int)$this->configuration->getManageStock();

        $condition = '
        (legacy_stock_item.use_config_manage_stock = 0 AND legacy_stock_item.manage_stock = 1)';
        if (1 === $globalManageStock) {
            $condition .= ' OR legacy_stock_item.use_config_manage_stock = 1';
        }

        return $condition;
    }
}
