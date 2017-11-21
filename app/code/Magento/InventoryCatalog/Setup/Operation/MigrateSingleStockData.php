<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Operation;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;

/**
 * Migrate Single Stock Item Data from CatalogInventory to Inventory Source Item with default source ID
 */
class MigrateSingleStockData
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * Insert Stock Item to Inventory Source Item by raw MySQL query
     *
     * @return void
     */
    public function execute()
    {
        $defaultSourceId = $this->defaultSourceProvider->getId();
        $sql = "INSERT INTO inventory_source_item (source_id, sku, quantity, status) 
                  SELECT $defaultSourceId AS source_id, product.sku, stock_item.qty, stock_item.is_in_stock 
                  FROM cataloginventory_stock_item AS stock_item 
                  JOIN catalog_product_entity AS product ON product.entity_id = stock_item.product_id";
        $this->resourceConnection->getConnection()->query($sql);
    }
}
