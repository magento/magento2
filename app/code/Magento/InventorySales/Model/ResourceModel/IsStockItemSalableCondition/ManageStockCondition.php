<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model\ResourceModel\IsStockItemSalableCondition;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\DB\Select;

/**
 * Condition for manage_stock configuration.
 */
class ManageStockCondition implements GetIsStockItemSalableConditionInterface
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
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Select $select): string
    {
        $globalManageStock = (int)$this->configuration->getManageStock();

        $condition = '(legacy_stock_item.use_config_manage_stock = 0 AND legacy_stock_item.manage_stock = 0)';
        if (0 === $globalManageStock) {
            $condition .= ' OR legacy_stock_item.use_config_manage_stock = 1';
        }

        return $condition;
    }
}
