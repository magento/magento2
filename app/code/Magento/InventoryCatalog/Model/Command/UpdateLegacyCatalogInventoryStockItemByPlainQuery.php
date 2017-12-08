<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Command;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\ReservationInterface;

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
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
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
            $connection->getTableName('cataloginventory_stock_item'),
            [
                StockItemInterface::QTY => new \Zend_Db_Expr(
                    sprintf('%s%s', StockItemInterface::QTY, $reservation->getQuantity())
                )
            ],
            [
                StockItemInterface::STOCK_ID . ' = ?' => $reservation->getStockId(),
                StockItemInterface::PRODUCT_ID . ' = ?' => $product->getId(),
                'website_id = ?' => 0
            ]
        );
    }
}
