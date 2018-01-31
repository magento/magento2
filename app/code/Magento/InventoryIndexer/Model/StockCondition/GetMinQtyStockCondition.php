<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model\StockCondition;

use Magento\CatalogInventory\Model\Configuration;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Condition for min_qty configuration.
 */
class GetMinQtyStockCondition implements GetStockConditionInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param Configuration $configuration
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        Configuration $configuration,
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
        $globalMinQty = $this->configuration->getMinQty();

        $qtyExpression = (string)$this->resourceConnection->getConnection()->getCheckSql(
            'source_item.' . SourceItemInterface::STATUS . ' = ' . SourceItemInterface::STATUS_OUT_OF_STOCK,
            0,
            SourceItemInterface::QUANTITY
        );
        $condition = sprintf(
            '((legacy_stock_item.use_config_min_qty = 1 AND ' . $qtyExpression . ' > %1$d)'
            . ' OR (legacy_stock_item.use_config_min_qty = 0 AND ' . $qtyExpression . ' > legacy_stock_item.min_qty))',
            $globalMinQty
        );

        return $condition;
    }
}
