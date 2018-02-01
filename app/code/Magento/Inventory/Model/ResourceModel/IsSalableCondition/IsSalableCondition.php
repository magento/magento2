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
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Chain of stock conditions.
 */
class IsSalableCondition implements GetIsSalableConditionInterface
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
        $select->joinInner(
            ['legacy_stock_item' => $this->resourceConnection->getTableName('cataloginventory_stock_item')],
            'product_entity.entity_id = legacy_stock_item.product_id',
            []
        );

        $globalMinQty = (float)$this->configuration->getMinQty();
        $globalManageStock = (int)$this->configuration->getManageStock();

        $quantityExpression = (string)$select->getConnection()->getCheckSql(
            'source_item.' . SourceItemInterface::STATUS . ' = ' . SourceItemInterface::STATUS_OUT_OF_STOCK,
            0,
            SourceItemInterface::QUANTITY
        );

        $isSalableString = sprintf(
            '(((legacy_stock_item.use_config_manage_stock = 1 AND 0 = %s)'
            . ' OR (legacy_stock_item.use_config_manage_stock = 0 AND legacy_stock_item.manage_stock = 0))'
            . ' OR ((legacy_stock_item.use_config_min_qty = 1 AND ' . $quantityExpression . ' > %s)'
            . ' OR (legacy_stock_item.use_config_min_qty = 0 AND'
            . ' ' . $quantityExpression . ' > legacy_stock_item.min_qty))'
            . ' OR product_entity.type_id = \'bundle\')',
            $globalManageStock,
            $globalMinQty
        );

        $isSalableExpression = $this->resourceConnection->getConnection()->getCheckSql($isSalableString, 1, 0);
        return (string)$isSalableExpression;
    }
}
