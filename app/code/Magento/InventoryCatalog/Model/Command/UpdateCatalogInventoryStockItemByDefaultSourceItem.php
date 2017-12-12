<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Command;

use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

/**
 * Legacy update cataloginventory_stock_item by plain MySql query.
 * Use for skip save by \Magento\CatalogInventory\Model\ResourceModel\Stock\Item::save
 */
class UpdateCatalogInventoryStockItemByDefaultSourceItem implements
    UpdateCatalogInventoryStockItemByDefaultSourceItemInterface
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
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @param ResourceConnection $resourceConnection
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

        if (!$productId) {
            throw new NoSuchEntityException(
                __('Product with SKU "%1" does not exist', $sourceItem->getSku())
            );
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $connection->getTableName('cataloginventory_stock_item'),
            [
                StockItemInterface::QTY => $sourceItem->getQuantity(),
            ],
            [
                StockItemInterface::STOCK_ID . ' = ?' => $this->defaultStockProvider->getId(),
                StockItemInterface::PRODUCT_ID . ' = ?' => $productId,
                'website_id = ?' => 0
            ]
        );
    }
}
