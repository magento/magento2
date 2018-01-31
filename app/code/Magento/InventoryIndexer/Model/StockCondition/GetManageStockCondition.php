<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model\StockCondition;

use Magento\CatalogInventory\Model\Configuration;

/**
 * Condition for manage_stock configuration.
 */
class GetManageStockCondition implements GetStockConditionInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @inheritdoc
     */
    public function execute(): string
    {
        $globalManageStock = $this->configuration->getManageStock();

        $condition = sprintf(
            '((legacy_stock_item.use_config_manage_stock = 1 AND 0 = %1$d)'
            . ' OR (legacy_stock_item.use_config_manage_stock = 0 AND legacy_stock_item.manage_stock = 0))',
            $globalManageStock
        );

        return $condition;
    }
}
