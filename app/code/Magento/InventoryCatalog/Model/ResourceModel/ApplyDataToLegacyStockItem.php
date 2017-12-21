<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;

/**
 * Apply data to legacy catalocinventory_stock_item table via plain MySql query
 */
class ApplyDataToLegacyStockItem
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
     * @param string $sku
     * @param float $quantity
     * @return void
     */
    public function execute(string $sku, float $quantity)
    {
        $productId = $this->getProductIdsBySkus->execute([$sku])[$sku];

        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $this->resourceConnection->getTableName('cataloginventory_stock_item'),
            [
                StockItemInterface::QTY => new \Zend_Db_Expr(sprintf('%s + %s', StockItemInterface::QTY, $quantity)),
            ],
            [
                StockItemInterface::PRODUCT_ID . ' = ?' => $productId,
                'website_id = ?' => 0,
            ]
        );
    }
}
