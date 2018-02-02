<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\IsSalableCondition;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;

/**
 * Condition for min_qty configuration.
 */
class GetMinQtyStockCondition implements GetIsSalableConditionInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $configuration;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param StockConfigurationInterface $configuration
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        StockConfigurationInterface $configuration,
        ResourceConnection $resourceConnection
    ) {
        $this->configuration = $configuration;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Select $select): string
    {
        $globalMinQty = (float)$this->configuration->getMinQty();

        // Don't apply source item status due to supposed that qty value is calculated only for in stock source items
        $condition = '(legacy_stock_item.use_config_min_qty = 1 AND quantity > ' . $globalMinQty . ')'
            . ' OR '
            . '(legacy_stock_item.use_config_min_qty = 0 AND quantity > legacy_stock_item.min_qty)';
        return $condition;
    }
}
