<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;

/**
 * Set to zero Legacy catalocinventory_stock_item database data via plain MySql query
 */
class SetToZeroLegacyStockItem
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
            $this->resourceConnection->getTableName('cataloginventory_stock_item'),
            [
                StockItemInterface::IS_IN_STOCK => 0,
                StockItemInterface::QTY => 0,
            ],
            [
                StockItemInterface::STOCK_ID . ' = ?' => $this->defaultSourceProvider->getId(),
                StockItemInterface::PRODUCT_ID . ' = ?' => $productId,
                'website_id = ?' => 0,
            ]
        );
    }
}
