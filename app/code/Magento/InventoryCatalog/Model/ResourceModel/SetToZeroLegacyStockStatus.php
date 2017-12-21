<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;

/**
 * Set to zero Legacy catalocinventory_stock_status database data via plain MySql query
 */
class SetToZeroLegacyStockStatus
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
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DefaultSourceProviderInterface $defaultSourceProvider,
        GetProductIdsBySkusInterface $getProductIdsBySkus
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
    }

    /**
     * @param string $sku
     * @return void
     */
    public function execute(string $sku)
    {
        $productId = $this->getProductIdsBySkus->execute([$sku])[$sku];

        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $this->resourceConnection->getTableName('cataloginventory_stock_status'),
            [
                StockStatusInterface::STOCK_STATUS => 0,
                StockStatusInterface::QTY => 0,
            ],
            [
                StockStatusInterface::STOCK_ID . ' = ?' => $this->defaultSourceProvider->getId(),
                StockStatusInterface::PRODUCT_ID . ' = ?' => $productId,
                'website_id = ?' => 0,
            ]
        );
    }
}
