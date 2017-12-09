<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Command;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;

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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param ResourceConnection $resourceConnection
     * @param ProductRepositoryInterface $productRepository
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        ProductRepositoryInterface $productRepository,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $sku, float $quantity)
    {
        $product = $this->productRepository->get($sku);
        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $this->resourceConnection->getTableName('cataloginventory_stock_status'),
            [
                StockStatusInterface::QTY => new \Zend_Db_Expr(sprintf('%s + %s', StockStatusInterface::QTY, $quantity))
            ],
            [
                StockStatusInterface::STOCK_ID . ' = ?' => $this->defaultSourceProvider->getId(),
                StockStatusInterface::PRODUCT_ID . ' = ?' => $product->getId(),
                'website_id = ?' => 0
            ]
        );
    }
}
