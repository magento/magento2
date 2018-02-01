<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel\IsSalableCondition;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

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
     */
    public function execute(): string
    {
        $globalMinQty = (float)$this->configuration->getMinQty();

        $condition = '(
            (config.use_config_min_qty = 1 AND ' . SourceItemInterface::QUANTITY . ' > ' . $globalMinQty . ')
            OR
            (config.use_config_min_qty = 0 AND ' . SourceItemInterface::QUANTITY . '  > config.min_qty)
        )';
        return $condition;
    }
}
