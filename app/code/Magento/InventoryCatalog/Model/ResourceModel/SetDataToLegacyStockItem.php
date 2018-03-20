<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\ResourceModel;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;
use Psr\Log\LoggerInterface;

/**
 * Set data to legacy cataloginventory_stock_item table via plain MySql query
 */
class SetDataToLegacyStockItem
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        LoggerInterface $logger
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->logger = $logger;
    }

    /**
     * @param string $sku
     * @param float $quantity
     * @param int $status
     * @return void
     */
    public function execute(string $sku, float $quantity, int $status)
    {
        try {
            $productId = $this->getProductIdsBySkus->execute([$sku])[$sku];

            $connection = $this->resourceConnection->getConnection();
            $connection->update(
                $this->resourceConnection->getTableName('cataloginventory_stock_item'),
                [
                    StockItemInterface::QTY => $quantity,
                    StockItemInterface::IS_IN_STOCK => $status,
                ],
                [
                    StockItemInterface::PRODUCT_ID . ' = ?' => $productId,
                    'website_id = ?' => 0,
                ]
            );
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
