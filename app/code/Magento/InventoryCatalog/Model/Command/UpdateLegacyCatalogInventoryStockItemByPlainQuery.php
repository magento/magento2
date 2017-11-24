<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Command;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalog\Api\UpdateLegacyCatalogInventoryStockItemByPlainQueryInterface;

/**
 * Legacy update cataloginventory_stock_item by plain MySql query.
 * Use for skip save by \Magento\CatalogInventory\Model\ResourceModel\Stock\Item::save
 */
class UpdateLegacyCatalogInventoryStockItemByPlainQuery implements
    UpdateLegacyCatalogInventoryStockItemByPlainQueryInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(StockItemInterface $stockItem)
    {
        $stockItemId = $stockItem->getItemId();
        $qty = $stockItem->getQty();
        $sql = "UPDATE cataloginventory_stock_item SET qty = $qty WHERE item_id = $stockItemId AND website_id = 0";

        $this->resourceConnection->getConnection()->query($sql);
    }
}
