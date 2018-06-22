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
 * Condition for backorders configuration.
 */
class BackordersCondition implements GetIsStockItemSalableConditionInterface
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
        $globalBackorders = (int)$this->configuration->getBackorders();

        $condition = (0 === $globalBackorders)
            ? 'legacy_stock_item.use_config_backorders = 1'
            : 'legacy_stock_item.use_config_backorders = 0 AND legacy_stock_item.backorders = 0';

        return $condition;
    }
}
