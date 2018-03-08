<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;

/**
 * Set data to legacy cataloginventory_stock_status table via plain MySql query
 */
class SetDataToLegacyStockStatus
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
     * @param int $status
     * @return void
     */
    public function execute(string $sku, float $quantity, int $status)
    {
        $productIds = $this->getProductIdsBySkus->execute([$sku]);

        if (isset($productIds[$sku])) {
            $productId = $productIds[$sku];

            $connection = $this->resourceConnection->getConnection();
            $connection->update(
                $this->resourceConnection->getTableName('cataloginventory_stock_status'),
                [
                    StockStatusInterface::QTY => $quantity,
                    StockStatusInterface::STOCK_STATUS => $status,
                ],
                [
                    StockStatusInterface::PRODUCT_ID . ' = ?' => $productId,
                    'website_id = ?' => 0,
                ]
            );
        }
    }
}
