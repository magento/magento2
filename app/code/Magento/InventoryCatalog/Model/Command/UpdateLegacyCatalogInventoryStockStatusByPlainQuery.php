<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Command;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalog\Api\UpdateLegacyCatalogInventoryStockStatusByPlainQueryInterface;

/**
 * Legacy update cataloginventory_stock_status by plain MySql query.
 * Use for skip save by \Magento\CatalogInventory\Model\ResourceModel\Stock\Item::save
 */
class UpdateLegacyCatalogInventoryStockStatusByPlainQuery implements
    UpdateLegacyCatalogInventoryStockStatusByPlainQueryInterface
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
    public function execute(StockStatusInterface $stockStatus)
    {
        $productId = $stockStatus->getProductId();
        $qty = $stockStatus->getQty();
        $sql = "UPDATE cataloginventory_stock_status SET qty = $qty WHERE product_id = $productId AND website_id = 0";

        $this->resourceConnection->getConnection()->query($sql);
    }
}
