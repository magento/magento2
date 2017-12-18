<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;

/**
 * Update Legacy catalocinventory_stock_status database data
 */
class UpdateLegacyStockStatusByPlainQuery
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
     * Execute Plain MySql query on catalaginventory_stock_status
     *
     * @param string $sku
     * @param float $quantity
     * @return void
     */
    public function execute(string $sku, float $quantity)
    {
        $productId = $this->getProductIdsBySkus->execute([$sku])[$sku];

        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $this->resourceConnection->getTableName('cataloginventory_stock_status'),
            [
                StockStatusInterface::QTY => new \Zend_Db_Expr(sprintf('%s + %s', StockStatusInterface::QTY, $quantity))
            ],
            [
                StockStatusInterface::STOCK_ID . ' = ?' => $this->defaultSourceProvider->getId(),
                StockStatusInterface::PRODUCT_ID . ' = ?' => $productId,
                'website_id = ?' => 0,
            ]
        );
    }
}
