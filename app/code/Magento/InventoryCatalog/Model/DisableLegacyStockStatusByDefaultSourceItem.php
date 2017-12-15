<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

/**
 * Delete Legacy cataloginventory_stock_status by plain MySql query
 * Use for skip delete by \Magento\CatalogInventory\Model\ResourceModel\Stock\Item::delete
 */
class DisableLegacyStockStatusByDefaultSourceItem
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param ProductIdLocatorInterface $productIdLocator
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DefaultSourceProviderInterface $defaultSourceProvider,
        DefaultStockProviderInterface $defaultStockProvider,
        ProductIdLocatorInterface $productIdLocator
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->productIdLocator = $productIdLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(SourceItemInterface $sourceItem)
    {
        if ($sourceItem->getSourceId() != $this->defaultSourceProvider->getId()) {
            return;
        }

        $productIds = $this->productIdLocator->retrieveProductIdsBySkus([$sourceItem->getSku()]);
        $productId = isset($productIds[$sourceItem->getSku()]) ? key($productIds[$sourceItem->getSku()]) : false;

        if ($productId) {
            $connection = $this->resourceConnection->getConnection();
            $connection->update(
                $this->resourceConnection->getTableName('cataloginventory_stock_status'),
                [
                    StockStatusInterface::STOCK_STATUS => 0,
                    StockStatusInterface::QTY => 0
                ],
                [
                    StockStatusInterface::STOCK_ID . ' = ?' => $this->defaultStockProvider->getId(),
                    StockStatusInterface::PRODUCT_ID . ' = ?' => $productId,
                    Stock::WEBSITE_ID . ' = ?' => 0
                ]
            );
        }
    }
}
