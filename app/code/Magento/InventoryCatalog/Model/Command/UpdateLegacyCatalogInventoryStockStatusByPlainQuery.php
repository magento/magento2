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
use Magento\InventoryApi\Api\Data\ReservationInterface;

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
     * @param ResourceConnection $resourceConnection
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ResourceConnection $resourceConnection, ProductRepositoryInterface $productRepository)
    {
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ReservationInterface $reservation)
    {
        $product = $this->productRepository->get($reservation->getSku());
        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $connection->getTableName('cataloginventory_stock_status'),
            [
                StockStatusInterface::QTY => new \Zend_Db_Expr(
                    sprintf('%s%s', StockStatusInterface::QTY, $reservation->getQuantity())
                )
            ],
            [
                StockStatusInterface::STOCK_ID . ' = ?' => $reservation->getStockId(),
                StockStatusInterface::PRODUCT_ID . ' = ?' => $product->getId(),
                'website_id = ?' => 0
            ]
        );
    }
}
