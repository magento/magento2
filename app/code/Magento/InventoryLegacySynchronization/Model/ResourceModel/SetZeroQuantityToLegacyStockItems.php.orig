<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Model\ResourceModel;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;

/**
 * Set quantity=0 to legacy cataloginventory_stock_item table for a set of skus via plain MySql query
 */
class SetZeroQuantityToLegacyStockItems
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetProductIdsBySkusInterface $getProductIdsBySkus
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
    }

    /**
     * @param array $skus
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(array $skus): void
    {
        $productIds = array_values($this->getProductIdsBySkus->execute($skus));

        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $this->resourceConnection->getTableName('cataloginventory_stock_item'),
            [
                StockItemInterface::QTY => 0,
                StockItemInterface::IS_IN_STOCK => 0,
            ],
            [
                StockItemInterface::PRODUCT_ID . ' IN (?)' => $productIds,
                'website_id = ?' => 0,
            ]
        );
    }
}
