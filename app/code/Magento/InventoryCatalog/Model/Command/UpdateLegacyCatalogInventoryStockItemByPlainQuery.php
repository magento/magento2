<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Command;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;

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
        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $connection->getTableName('cataloginventory_stock_item'),
            [StockItemInterface::QTY => $stockItem->getQty()],
            [StockItemInterface::ITEM_ID . ' = ?' => $stockItem->getItemId(), 'website_id = ?' => 0]
        );
    }
}
